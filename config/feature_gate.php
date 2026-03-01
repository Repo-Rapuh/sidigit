<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feature Registry
    |--------------------------------------------------------------------------
    | Daftar feature utama aplikasi. Nilai true/false menentukan default
    | aktif/tidak aktif di level global.
    */
    'features' => [
        'dashboard' => true,
        'customers' => true,
        'orders' => true,
        'payments' => true,
        'public_tracking' => true,
        'stocks' => true,
        'expenses' => true,
        'reports' => true,
        'accounting' => true,
        'audit_logs' => true,
        'multi_branch' => true,
        'rbac_advanced' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Bypass Roles
    |--------------------------------------------------------------------------
    | Role berikut selalu lolos pengecekan feature gate.
    */
    'bypass_role_slugs' => [
        'superadmin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Overrides
    |--------------------------------------------------------------------------
    | Override per role terhadap feature tertentu.
    | - true/false untuk feature spesifik
    | - key "*" untuk default semua feature role tersebut
    */
    'role_overrides' => [
        // 'owner' => ['*' => true],
        // 'kasir' => ['accounting' => false, 'audit_logs' => false],
    ],
];

