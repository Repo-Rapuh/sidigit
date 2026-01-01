<?php

namespace App\Enums;

enum CustomerMemberType: string
{
    case UMUM = 'umum';
    case RESELLER = 'reseller';
    case SEKOLAH = 'sekolah';
    case PEMERINTAH = 'pemerintah';
    case SWASTA = 'swasta';

    public function label(): string
    {
        return match ($this) {
            self::UMUM => 'Umum',
            self::RESELLER => 'Reseller',
            self::SEKOLAH => 'Sekolah',
            self::PEMERINTAH => 'Pemerintah',
            self::SWASTA => 'Swasta',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::UMUM => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
            self::RESELLER => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-200',
            self::SEKOLAH => 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-200',
            self::PEMERINTAH => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/15 dark:text-yellow-200',
            self::SWASTA => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-200',
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
