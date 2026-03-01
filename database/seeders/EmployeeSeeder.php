<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        Employee::updateOrCreate([
            'email' => 'kasir@example.com',
        ], [
            'name' => 'Kasir',
            'address' => 'Leuwiliang',
            'phone_number' => '0800000001',
            'salary' => 0,
            'status' => 'active',
        ]);

        Employee::updateOrCreate([
            'email' => 'desainer@gmail.com',
        ], [
            'name' => 'Desainer',
            'address' => 'Leuwiliang',
            'phone_number' => '0800000002',
            'salary' => 0,
            'status' => 'active',
        ]);

        Employee::updateOrCreate([
            'email' => 'operator@gmail.com',
        ], [
            'name' => 'Operator',
            'address' => 'Leuwiliang',
            'phone_number' => '0800000003',
            'salary' => 0,
            'status' => 'active',
        ]);

        Employee::updateOrCreate([
            'email' => 'finishing@example.com',
        ], [
            'name' => 'Operator',
            'address' => 'Leuwiliang',
            'phone_number' => '0800000001',
            'salary' => 0,
            'status' => 'active',
        ]);
    }
}
