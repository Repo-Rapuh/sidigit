<?php

namespace App\Enums;

enum EmployeeStatus: string
{
    case INACTIVE = 'inactive';
    case ACTIVE = 'active';

    public function label(): string
    {
        return match ($this) {
            self::INACTIVE => 'Tidak Aktif',
            self::ACTIVE => 'Aktif',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::INACTIVE => 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-300',
            self::ACTIVE => 'bg-success-100 text-success-600 dark:bg-success-500/15 dark:text-success-200',
        };
    }

    public static function options(): array
    {
        return array_map(
            static fn (self $case) => $case->value,
            self::cases()
        );
    }
}
