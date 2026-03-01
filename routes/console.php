<?php

use App\Models\Branch;
use App\Models\Employee;
use App\Support\UploadPath;
use App\Support\UploadStorage;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('uploads:migrate-branch-prefix {--dry-run}', function () {
    $disk = UploadStorage::disk();
    $storage = Storage::disk($disk);
    $dryRun = (bool) $this->option('dry-run');

    $moved = 0;
    $skipped = 0;
    $failed = 0;

    $nextTarget = function (string $target) use ($storage): string {
        $target = ltrim($target, '/');
        if (! $storage->exists($target)) {
            return $target;
        }

        $extension = pathinfo($target, PATHINFO_EXTENSION);
        $filename = pathinfo($target, PATHINFO_FILENAME);
        $directory = trim(dirname($target), '.');
        $suffix = now()->format('YmdHis');
        $candidate = ($directory !== '' ? $directory . '/' : '') . $filename . '-' . $suffix . ($extension ? '.' . $extension : '');

        return $candidate;
    };

    $movePath = function (string $source, string $target) use ($storage, $dryRun, &$moved, &$skipped, &$failed): ?string {
        $source = ltrim($source, '/');
        $target = ltrim($target, '/');

        if ($source === $target) {
            $skipped++;
            return $source;
        }

        if (! $storage->exists($source)) {
            $failed++;
            return null;
        }

        if ($dryRun) {
            $moved++;
            return $target;
        }

        if (! $storage->copy($source, $target)) {
            $failed++;
            return null;
        }

        $storage->delete($source);
        $moved++;

        return $target;
    };

    Branch::withTrashed()->chunkById(200, function ($branches) use ($nextTarget, $movePath) {
        foreach ($branches as $branch) {
            $updates = [];

            if (filled($branch->logo_path) && ! Str::startsWith($branch->logo_path, UploadPath::branchPrefix((int) $branch->id))) {
                $target = UploadPath::branchPrefix((int) $branch->id) . 'branches/logos/' . basename((string) $branch->logo_path);
                $target = $nextTarget($target);
                $newPath = $movePath((string) $branch->logo_path, $target);
                if ($newPath) {
                    $updates['logo_path'] = $newPath;
                }
            }

            if (filled($branch->qris_path) && ! Str::startsWith($branch->qris_path, UploadPath::branchPrefix((int) $branch->id))) {
                $target = UploadPath::branchPrefix((int) $branch->id) . 'branches/qris/' . basename((string) $branch->qris_path);
                $target = $nextTarget($target);
                $newPath = $movePath((string) $branch->qris_path, $target);
                if ($newPath) {
                    $updates['qris_path'] = $newPath;
                }
            }

            if (! empty($updates)) {
                $branch->forceFill($updates)->save();
            }
        }
    });

    Employee::withTrashed()->chunkById(200, function ($employees) use ($nextTarget, $movePath) {
        foreach ($employees as $employee) {
            if (! filled($employee->photo)) {
                continue;
            }

            $branchId = (int) ($employee->branch_id ?: 1);
            if (Str::startsWith($employee->photo, UploadPath::branchPrefix($branchId))) {
                continue;
            }

            $target = UploadPath::branchPrefix($branchId) . 'employees/photos/' . basename((string) $employee->photo);
            $target = $nextTarget($target);
            $newPath = $movePath((string) $employee->photo, $target);
            if ($newPath) {
                $employee->forceFill(['photo' => $newPath])->save();
            }
        }
    });

    $this->newLine();
    $this->info('Migrasi path upload selesai.');
    $this->line('Disk: ' . $disk);
    $this->line('Mode: ' . ($dryRun ? 'dry-run' : 'apply'));
    $this->line('Moved: ' . $moved);
    $this->line('Skipped: ' . $skipped);
    $this->line('Failed: ' . $failed);
})->purpose('Pindahkan path upload lama ke format /{branch_id}/...');
