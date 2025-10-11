<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Panggil semua seeder dalam urutan yang logis
        $this->call([
            RoleSeeder::class,           // 1. Buat Role
            MenuSeeder::class,           // 2. Buat Menu
            PermissionSeeder::class,     // 3. Buat Permission
            RolePermissionSeeder::class, // 4. Berikan Hak Akses
            UserSeeder::class,           // 5. Buat User
        ]);
    }
}
