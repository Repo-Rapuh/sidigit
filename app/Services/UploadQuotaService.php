<?php

namespace App\Services;

use App\Support\UploadStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadQuotaService
{
    public function assertCanUpload(UploadedFile $file, ?string $replacedPath = null): void
    {
        $quotaBytes = $this->quotaBytes();
        if ($quotaBytes <= 0) {
            return;
        }

        $currentUsedBytes = $this->usedBytes();
        $incomingSize = max(0, (int) ($file->getSize() ?? 0));
        $replacedSize = $this->fileSize($replacedPath);
        $projectedUsedBytes = max(0, $currentUsedBytes - $replacedSize + $incomingSize);

        if ($projectedUsedBytes <= $quotaBytes) {
            return;
        }

        $remainingBytes = max(0, $quotaBytes - max(0, $currentUsedBytes - $replacedSize));

        throw new \RuntimeException(sprintf(
            'Storage klien penuh. Kuota %s, terpakai %s. Sisa %s, file upload %s. Hapus file lama atau naikkan kuota.',
            $this->formatBytes($quotaBytes),
            $this->formatBytes(max(0, $currentUsedBytes - $replacedSize)),
            $this->formatBytes($remainingBytes),
            $this->formatBytes($incomingSize)
        ));
    }

    protected function quotaBytes(): int
    {
        return max(0, (int) config('filesystems.upload_quota_bytes', 0));
    }

    protected function usedBytes(): int
    {
        $disk = UploadStorage::disk();
        $total = 0;

        try {
            $paths = Storage::disk($disk)->allFiles('');
        } catch (\Throwable $e) {
            $paths = [];
        }

        foreach ($paths as $path) {
            $path = ltrim((string) $path, '/');
            if (! $this->isClientUploadPath($path)) {
                continue;
            }

            try {
                $total += (int) Storage::disk($disk)->size($path);
            } catch (\Throwable $e) {
                // Abaikan file yang gagal dibaca agar upload tidak memblokir total.
            }
        }

        return max(0, $total);
    }

    protected function fileSize(?string $path): int
    {
        if (blank($path)) {
            return 0;
        }

        $disk = UploadStorage::disk();

        try {
            return max(0, (int) Storage::disk($disk)->size(ltrim((string) $path, '/')));
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function formatBytes(int $bytes): string
    {
        $bytes = max(0, $bytes);
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $value = (float) $bytes;
        $unitIndex = 0;

        while ($value >= 1024 && $unitIndex < count($units) - 1) {
            $value /= 1024;
            $unitIndex++;
        }

        $precision = $unitIndex === 0 ? 0 : 2;

        return number_format($value, $precision, ',', '.') . ' ' . $units[$unitIndex];
    }

    protected function isClientUploadPath(string $path): bool
    {
        if (preg_match('/^\d+\//', $path) === 1) {
            return true;
        }

        foreach (['branches/', 'employees/', 'orders/attachments/'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
