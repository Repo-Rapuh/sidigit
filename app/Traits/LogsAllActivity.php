<?php

namespace App\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait LogsAllActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            // Menggunakan nama model sebagai nama log secara dinamis
            ->useLogName(class_basename($this))
            // Mencatat semua atribut yang bisa diisi ($fillable)
            ->logFillable()
            // Hanya mencatat jika ada perubahan
            ->logOnlyDirty()
            // Deskripsi event yang dinamis
            ->setDescriptionForEvent(fn(string $eventName) => "Data ini telah di-{$eventName}");
    }
}
