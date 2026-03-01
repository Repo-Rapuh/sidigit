<?php

namespace App\Support;

class UploadStorage
{
    public static function disk(): string
    {
        $fallbackDisk = 'public';
        $disk = (string) config('filesystems.upload_disk', $fallbackDisk);
        $diskConfig = config("filesystems.disks.{$disk}", []);

        if (! is_array($diskConfig) || empty($diskConfig)) {
            return $fallbackDisk;
        }

        $driver = (string) ($diskConfig['driver'] ?? '');
        if ($driver !== 's3') {
            return $disk;
        }

        $requiredKeys = ['key', 'secret', 'bucket'];
        foreach ($requiredKeys as $key) {
            if (blank($diskConfig[$key] ?? null)) {
                return $fallbackDisk;
            }
        }

        return $disk;
    }

    public static function deletionDisks(): array
    {
        $disk = self::disk();

        return collect([$disk, 'public'])
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
