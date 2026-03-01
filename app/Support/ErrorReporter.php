<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ErrorReporter
{
    public static function report(Throwable $th, string $fallback): array
    {
        $reference = self::reference();
        $showDetails = self::shouldShowDetails();

        Log::error($fallback, [
            'ref' => $reference,
            'exception' => $th,
        ]);

        return [
            'ref' => $reference,
            'message' => ErrorMessage::for($th, $fallback, $showDetails, $reference),
        ];
    }

    public static function shouldShowDetails(): bool
    {
        if (app()->environment('local')) {
            return true;
        }

        $user = auth()->user();
        if (!$user) {
            return false;
        }

        $adminSlugs = ['superadmin', 'super-admin', 'admin', 'owner'];
        $adminNames = ['superadmin', 'super admin', 'admin', 'owner'];

        return $user->roles()
            ->where(function ($query) use ($adminSlugs, $adminNames) {
                $query->whereIn('slug', $adminSlugs)
                    ->orWhereIn('name', $adminNames);
            })
            ->exists();
    }

    protected static function reference(): string
    {
        return 'E-' . now()->format('ymdHi') . '-' . Str::upper(Str::random(3));
    }
}
