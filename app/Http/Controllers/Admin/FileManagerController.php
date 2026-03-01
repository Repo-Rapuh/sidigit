<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Support\FileManagerThumbnail;
use App\Support\UploadStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileManagerController extends Controller
{
    public function download(Request $request): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        $path = $this->resolveAllowedPath($request, $user);
        $disk = $this->resolveReadableDisk($path);

        return Storage::disk($disk)->download($path, basename($path));
    }

    public function thumbnail(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 403);

        $path = $this->resolveAllowedPath($request, $user);
        abort_unless($this->isImagePath($path), 404);

        $disk = $this->resolveReadableDisk($path);
        $thumbPath = FileManagerThumbnail::relativePath($path);

        if (! Storage::disk('public')->exists($thumbPath)) {
            $this->generateThumbnail($disk, $path, $thumbPath);
        }

        $absolutePath = Storage::disk('public')->path($thumbPath);
        abort_unless(is_file($absolutePath), 404);

        return response()->file($absolutePath, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    protected function allowedBranchIds($user): Collection
    {
        if ($user->hasRoleSlug('superadmin')) {
            return Branch::query()
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values();
        }

        return collect([$user->branch_id])
            ->merge($user->branches()->pluck('branches.id'))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }

    protected function resolveAllowedPath(Request $request, $user): string
    {
        $encodedPath = (string) $request->query('path', '');
        $decodedPath = base64_decode($encodedPath, true);
        abort_unless(is_string($decodedPath) && $decodedPath !== '', 404);

        $path = ltrim($decodedPath, '/');
        abort_unless(preg_match('/^\d+\//', $path) === 1, 403);

        $branchId = (int) Str::before($path, '/');
        abort_unless($this->allowedBranchIds($user)->contains($branchId), 403);

        return $path;
    }

    protected function resolveReadableDisk(string $path): string
    {
        $disk = collect(UploadStorage::deletionDisks())
            ->first(fn (string $candidateDisk) => Storage::disk($candidateDisk)->exists($path));
        abort_unless(filled($disk), 404);

        return (string) $disk;
    }

    protected function isImagePath(string $path): bool
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp'], true);
    }

    protected function generateThumbnail(string $disk, string $path, string $thumbPath): void
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagejpeg')) {
            abort(404);
        }

        $stream = Storage::disk($disk)->readStream($path);
        if ($stream === false) {
            abort(404);
        }

        try {
            $contents = stream_get_contents($stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        if ($contents === false || $contents === '') {
            abort(404);
        }

        $source = @imagecreatefromstring($contents);
        if ($source === false) {
            abort(404);
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        if ($sourceWidth < 1 || $sourceHeight < 1) {
            imagedestroy($source);
            abort(404);
        }

        $maxSize = 96;
        $ratio = min($maxSize / $sourceWidth, $maxSize / $sourceHeight, 1);
        $thumbWidth = max(1, (int) round($sourceWidth * $ratio));
        $thumbHeight = max(1, (int) round($sourceHeight * $ratio));

        $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
        if ($thumb === false) {
            imagedestroy($source);
            abort(404);
        }

        $background = imagecolorallocate($thumb, 255, 255, 255);
        imagefill($thumb, 0, 0, $background);
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $sourceWidth, $sourceHeight);

        ob_start();
        imagejpeg($thumb, null, 82);
        $binary = ob_get_clean();

        imagedestroy($thumb);
        imagedestroy($source);

        if ($binary === false || $binary === '') {
            abort(404);
        }

        Storage::disk('public')->put($thumbPath, $binary);
    }
}
