<?php

namespace App\Services;

use App\Support\FileManagerThumbnail;
use App\Support\UploadPath;
use App\Support\UploadStorage;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileManagerService
{
    public function paginateByBranch(
        int $branchId,
        string $folder = 'all',
        string $search = '',
        string $sortDirection = 'desc',
        int $perPage = 20,
        int $page = 1
    ): LengthAwarePaginator {
        $disk = UploadStorage::disk();
        $branchPrefix = UploadPath::branchPrefix($branchId);
        $files = $this->listFiles($disk, $branchPrefix);
        $files = $this->filterByFolder($files, $branchPrefix, $folder);

        if (filled($search)) {
            $needle = Str::lower($search);
            $files = $files->filter(fn (string $path) => Str::contains(Str::lower($path), $needle));
        }

        $files = $files
            ->values()
            ->sort(fn (string $a, string $b) => strnatcasecmp($a, $b))
            ->values();

        if (Str::lower($sortDirection) !== 'asc') {
            $files = $files->reverse()->values();
        }

        $perPage = max(10, min(100, $perPage));
        $page = max(1, $page);
        $total = $files->count();
        $pagePaths = $files->forPage($page, $perPage)->values();

        // Optimasi: metadata (size/last_modified/url) hanya dihitung untuk item halaman aktif.
        $items = $pagePaths
            ->map(fn (string $path) => $this->toFileRow($disk, $branchId, $path))
            ->filter()
            ->values();

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    public function folderOptions(int $branchId): Collection
    {
        $disk = UploadStorage::disk();
        $cacheKey = $this->cacheKey("folder-options:{$disk}:{$branchId}");
        $seconds = $this->cacheTtlSeconds();

        $options = Cache::remember($cacheKey, now()->addSeconds($seconds), function () use ($disk, $branchId) {
            $prefix = UploadPath::branchPrefix($branchId);
            $files = $this->listFiles($disk, $prefix);

            $detectedFolders = $files
                ->map(function (string $path) use ($prefix) {
                    $relative = Str::after($path, $prefix);
                    $directory = trim(dirname($relative), './');

                    return $directory === '' ? null : $directory;
                })
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->all();

            $defaults = [
                'branches/logos',
                'branches/qris',
                'employees/photos',
                'orders/attachments',
            ];

            return collect($defaults)
                ->merge($detectedFolders)
                ->unique()
                ->sort()
                ->values()
                ->all();
        });

        return collect($options);
    }

    public function deleteByBranch(int $branchId, string $path): void
    {
        $path = ltrim($path, '/');
        $allowedPrefix = UploadPath::branchPrefix($branchId);

        if (! Str::startsWith($path, $allowedPrefix)) {
            throw new \RuntimeException('Akses file tidak valid untuk cabang aktif.');
        }

        foreach (UploadStorage::deletionDisks() as $disk) {
            Storage::disk($disk)->delete($path);
        }
        Storage::disk('public')->delete(FileManagerThumbnail::relativePath($path));

        $this->forgetCacheForBranch($branchId);
    }

    public function temporaryUrl(string $path, int $minutes = 10): ?string
    {
        $disk = UploadStorage::disk();
        $path = ltrim($path, '/');
        $driver = config("filesystems.disks.{$disk}.driver");

        try {
            $storage = Storage::disk($disk);

            return $driver === 's3'
                ? $storage->temporaryUrl($path, now()->addMinutes($minutes))
                : $storage->url($path);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function storageDetails(int $branchId): array
    {
        $disk = UploadStorage::disk();
        $branchStats = $this->branchStorageStats($disk, $branchId);
        $clientStats = $this->clientStorageStats($disk);

        $quotaBytes = max(0, (int) config('filesystems.upload_quota_bytes', 0));
        $remainingBytes = $quotaBytes > 0 ? max(0, $quotaBytes - $clientStats['client_total_size']) : null;
        $usedPercent = $quotaBytes > 0 ? round(min(100, ($clientStats['client_total_size'] / $quotaBytes) * 100), 2) : null;

        return [
            'total_files' => $branchStats['total_files'],
            'total_size' => $branchStats['branch_total_size'],
            'branch_total_size' => $branchStats['branch_total_size'],
            'client_total_size' => $clientStats['client_total_size'],
            'client_total_files' => $clientStats['client_total_files'],
            'quota_bytes' => $quotaBytes,
            'remaining_bytes' => $remainingBytes,
            'used_percent' => $usedPercent,
            'image_count' => $branchStats['image_count'],
            'document_count' => $branchStats['document_count'],
            'other_count' => $branchStats['other_count'],
            'folder_usage' => [],
            'latest_files' => [],
        ];
    }

    public function forgetCacheForBranch(int $branchId): void
    {
        $disk = UploadStorage::disk();
        $branchPrefix = UploadPath::branchPrefix($branchId);

        Cache::forget($this->cacheKey("list:{$disk}:" . md5($branchPrefix)));
        Cache::forget($this->cacheKey("folder-options:{$disk}:{$branchId}"));
        Cache::forget($this->cacheKey("storage-details:branch:{$disk}:{$branchId}"));
        Cache::forget($this->cacheKey("storage-details:client:{$disk}"));
        Cache::forget($this->cacheKey("list:{$disk}:" . md5('')));
    }

    protected function toFileRow(string $disk, int $branchId, string $path): ?array
    {
        $path = ltrim($path, '/');
        $branchPrefix = UploadPath::branchPrefix($branchId);
        $relativePath = Str::after($path, $branchPrefix);
        $filename = basename($path);
        $directory = trim(dirname($relativePath), './');
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $size = $this->safeFileSize($disk, $path);
        $lastModified = $this->safeLastModified($disk, $path);
        $isImage = $this->isImageExtension($extension);
        $mime = $this->mimeFromExtension($extension);
        $canGenerateThumbnail = function_exists('imagecreatefromstring') && function_exists('imagejpeg');

        $url = $this->temporaryUrl($path, 15);
        $previewUrl = ($isImage && $canGenerateThumbnail)
            ? route('file-manager.thumbnail', ['path' => base64_encode($path)])
            : null;

        return [
            'path' => $path,
            'filename' => $filename,
            'directory' => $directory === '' ? '-' : $directory,
            'mime' => $mime,
            'extension' => $extension ?: '-',
            'size' => $size,
            'last_modified' => $lastModified,
            'updated_at' => $lastModified > 0 ? Carbon::createFromTimestamp($lastModified) : null,
            'is_image' => $isImage,
            'url' => $url,
            'preview_url' => $previewUrl,
        ];
    }

    protected function listFiles(string $disk, string $prefix): Collection
    {
        $normalizedPrefix = ltrim($prefix, '/');
        $cacheKey = $this->cacheKey("list:{$disk}:" . md5($normalizedPrefix));
        $seconds = $this->cacheTtlSeconds();

        $paths = Cache::remember($cacheKey, now()->addSeconds($seconds), function () use ($disk, $normalizedPrefix) {
            try {
                return collect(Storage::disk($disk)->allFiles($normalizedPrefix))
                    ->map(fn (string $path) => ltrim($path, '/'))
                    ->values()
                    ->all();
            } catch (\Throwable $e) {
                return [];
            }
        });

        return collect($paths);
    }

    protected function filterByFolder(Collection $files, string $branchPrefix, string $folder): Collection
    {
        if ($folder === '' || $folder === 'all') {
            return $files;
        }

        $folderPrefix = $branchPrefix . trim($folder, '/') . '/';

        return $files->filter(fn (string $path) => Str::startsWith($path, $folderPrefix))->values();
    }

    protected function branchStorageStats(string $disk, int $branchId): array
    {
        $cacheKey = $this->cacheKey("storage-details:branch:{$disk}:{$branchId}");
        $seconds = $this->cacheTtlSeconds();

        return Cache::remember($cacheKey, now()->addSeconds($seconds), function () use ($disk, $branchId) {
            $prefix = UploadPath::branchPrefix($branchId);
            $paths = $this->listFiles($disk, $prefix);

            $branchTotalSize = 0;
            $imageCount = 0;
            $documentCount = 0;
            $otherCount = 0;

            foreach ($paths as $path) {
                $filename = basename((string) $path);
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $size = $this->safeFileSize($disk, (string) $path);
                $branchTotalSize += $size;

                if ($this->isImageExtension($extension)) {
                    $imageCount++;
                } elseif ($this->isDocumentExtension($extension)) {
                    $documentCount++;
                } else {
                    $otherCount++;
                }
            }

            return [
                'total_files' => $paths->count(),
                'branch_total_size' => $branchTotalSize,
                'image_count' => $imageCount,
                'document_count' => $documentCount,
                'other_count' => $otherCount,
            ];
        });
    }

    protected function clientStorageStats(string $disk): array
    {
        $cacheKey = $this->cacheKey("storage-details:client:{$disk}");
        $seconds = $this->cacheTtlSeconds();

        return Cache::remember($cacheKey, now()->addSeconds($seconds), function () use ($disk) {
            $paths = $this->allClientUploadPaths($disk);
            $totalSize = 0;

            foreach ($paths as $path) {
                $totalSize += $this->safeFileSize($disk, (string) $path);
            }

            return [
                'client_total_size' => $totalSize,
                'client_total_files' => $paths->count(),
            ];
        });
    }

    protected function allClientUploadPaths(string $disk): Collection
    {
        $paths = $this->listFiles($disk, '');

        return $paths
            ->filter(fn (string $path) => $this->isClientUploadPath($path))
            ->values();
    }

    protected function isClientUploadPath(string $path): bool
    {
        if (preg_match('/^\d+\//', $path) === 1) {
            return true;
        }

        foreach (['branches/', 'employees/', 'orders/attachments/'] as $prefix) {
            if (Str::startsWith($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    protected function safeFileSize(string $disk, string $path): int
    {
        try {
            return max(0, (int) Storage::disk($disk)->size(ltrim($path, '/')));
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function safeLastModified(string $disk, string $path): int
    {
        try {
            return max(0, (int) Storage::disk($disk)->lastModified(ltrim($path, '/')));
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function isImageExtension(string $extension): bool
    {
        return in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'bmp'], true);
    }

    protected function isDocumentExtension(string $extension): bool
    {
        return in_array(strtolower($extension), [
            'txt', 'csv', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'json', 'xml', 'md',
        ], true);
    }

    protected function mimeFromExtension(string $extension): string
    {
        $extension = strtolower($extension);

        $map = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'bmp' => 'image/bmp',
            'pdf' => 'application/pdf',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'md' => 'text/markdown',
        ];

        return $map[$extension] ?? 'application/octet-stream';
    }

    protected function cacheTtlSeconds(): int
    {
        return max(10, (int) config('filesystems.file_manager_cache_ttl_seconds', 60));
    }

    protected function cacheKey(string $suffix): string
    {
        return 'file-manager:' . $suffix;
    }
}
