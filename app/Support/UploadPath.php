<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class UploadPath
{
    public static function branchLogo(int $branchId, UploadedFile $file): string
    {
        return self::build($branchId, 'branches/logos', $file);
    }

    public static function branchQris(int $branchId, UploadedFile $file): string
    {
        return self::build($branchId, 'branches/qris', $file);
    }

    public static function employeePhoto(int $branchId, UploadedFile $file): string
    {
        return self::build($branchId, 'employees/photos', $file);
    }

    public static function branchPrefix(int $branchId): string
    {
        return trim((string) $branchId, '/') . '/';
    }

    protected static function build(int $branchId, string $directory, UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';
        $filename = Str::uuid()->toString() . '.' . strtolower($extension);

        return trim((string) $branchId, '/') . '/' . trim($directory, '/') . '/' . $filename;
    }
}

