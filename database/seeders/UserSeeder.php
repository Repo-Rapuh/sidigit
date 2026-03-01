<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil role yang dibutuhkan
        $superadminRole = Role::where('name', 'Superadmin')->first()
            ?? Role::where('slug', 'superadmin')->first();
        $ownerRole = Role::where('name', 'Owner')->first()
            ?? Role::where('slug', 'owner')->first();
        $kasirRole = Role::where('name', 'Kasir')->first()
            ?? Role::where('slug', 'kasir')->first();
        $desainerRole = Role::where('name', 'Desainer')->first()
            ?? Role::where('slug', 'desainer')->first();
        $operatorRole = Role::where('name', 'Operator')->first()
            ?? Role::where('slug', 'operator')->first();

        if (!$superadminRole) {
            return;
        }

        $mainBranch = Branch::where('is_main', true)->first();
        if (!$mainBranch) {
            $mainBranch = Branch::create([
                'name' => config('app.name', 'Percetakan') . ' (Induk)',
                'address' => config('app.company_address', 'Alamat belum diatur.'),
                'phone' => config('app.company_phone', '-'),
                'email' => config('mail.from.address', '-'),
                'is_main' => true,
            ]);
        }

        // 2. Buat/update user superadmin dan berikan role superadmin
        $superadminUser = User::updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Superadmin User',
                'username' => 'superadmin',
                'password' => Hash::make('password'),
                'branch_id' => $mainBranch->id,
            ]
        );
        $superadminUser->roles()->syncWithoutDetaching([$superadminRole->id]);
        $superadminUser->branches()->syncWithoutDetaching([$mainBranch->id]);

        // 3. Buat/update user owner default (hak akses tertinggi tenant)
        if ($ownerRole) {
            $ownerUser = User::updateOrCreate(
                ['email' => 'owner@gmail.com'],
                [
                    'name' => 'owner',
                    'username' => 'owner',
                    'password' => Hash::make('password'),
                    'branch_id' => $mainBranch->id,
                ]
            );

            $ownerUser->roles()->syncWithoutDetaching([$ownerRole->id]);
            $ownerUser->branches()->syncWithoutDetaching([$mainBranch->id]);
        }

        // 4. Buat/update user kasir default
        if ($kasirRole) {
            $kasirUser = User::updateOrCreate(
                ['email' => 'kasir@gmail.com'],
                [
                    'name' => 'kasir',
                    'username' => 'kasir',
                    'password' => Hash::make('password'),
                    'branch_id' => $mainBranch->id,
                ]
            );

            $kasirUser->roles()->syncWithoutDetaching([$kasirRole->id]);
            $kasirUser->branches()->syncWithoutDetaching([$mainBranch->id]);
        }

        // 5. Buat/update user desainer + kaitkan ke data karyawan desainer
        if ($desainerRole) {
            $desainerEmployee = Employee::updateOrCreate(
                ['email' => 'desainer@gmail.com'],
                [
                    'name' => 'Desainer',
                    'address' => 'Leuwiliang',
                    'phone_number' => '0800000002',
                    'salary' => 0,
                    'status' => 'active',
                    'branch_id' => $mainBranch->id,
                ]
            );

            $desainerUser = User::updateOrCreate(
                ['email' => 'desainer@gmail.com'],
                [
                    'name' => 'desainer',
                    'username' => 'desainer',
                    'password' => Hash::make('password'),
                    'branch_id' => $mainBranch->id,
                    'employee_id' => $desainerEmployee->id,
                ]
            );

            $desainerUser->roles()->syncWithoutDetaching([$desainerRole->id]);
            $desainerUser->branches()->syncWithoutDetaching([$mainBranch->id]);
        }

        // 6. Buat/update user operator + kaitkan ke data karyawan operator
        if ($operatorRole) {
            $operatorEmployee = Employee::updateOrCreate(
                ['email' => 'operator@gmail.com'],
                [
                    'name' => 'Operator',
                    'address' => 'Leuwiliang',
                    'phone_number' => '0800000003',
                    'salary' => 0,
                    'status' => 'active',
                    'branch_id' => $mainBranch->id,
                ]
            );

            $operatorUser = User::updateOrCreate(
                ['email' => 'operator@gmail.com'],
                [
                    'name' => 'operator',
                    'username' => 'operator',
                    'password' => Hash::make('password'),
                    'branch_id' => $mainBranch->id,
                    'employee_id' => $operatorEmployee->id,
                ]
            );

            $operatorUser->roles()->syncWithoutDetaching([$operatorRole->id]);
            $operatorUser->branches()->syncWithoutDetaching([$mainBranch->id]);
        }
    }
}
