<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Gunakan updateOrCreate agar tidak duplikat jika seeder dijalankan ulang
        Role::updateOrCreate(
            ['name' => 'Superadmin'],
            ['name' => 'Superadmin']
        );

        Role::updateOrCreate(
            ['name' => 'Kasir'],
            ['name' => 'Kasir']
        );

        Role::updateOrCreate(
            ['name' => 'Desainer'],
            ['name' => 'Desainer']
        );

        Role::updateOrCreate(
            ['name' => 'Operator'],
            ['name' => 'Operator']
        );

        Role::updateOrCreate(
            ['name' => 'Finishing'],
            ['name' => 'Finishing']
        );

        Role::updateOrCreate(
            ['name' => 'Owner'],
            ['name' => 'Owner']
        );
    }
}
