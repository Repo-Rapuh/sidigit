<?php

namespace App\Support;

class FileManagerThumbnail
{
    public static function relativePath(string $sourcePath): string
    {
        $normalized = ltrim($sourcePath, '/');
        $hash = sha1($normalized);

        return "file-manager-thumbnails/{$hash}.jpg";
    }
}

