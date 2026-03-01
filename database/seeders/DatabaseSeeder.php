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
            BranchSeeder::class,         // 5. Cabang Induk
            AccountingAccountSeeder::class, // 6. COA default
            UserSeeder::class,              // 7. Buat User
            UnitSeeder::class,              // 8. Master Satuan
            CategorySeeder::class,          // 9. Master Kategori
            SupplierSeeder::class,          // 10. Master Supplier
            WarehouseSeeder::class,         // 11. Master Gudang
            MaterialSeeder::class,          // 12. Master Material
            ProductSeeder::class,           // 13. Master Produk
            FinishSeeder::class,            // 14. Master Finishing
            CustomerSeeder::class,          // 15. Master Customer
            EmployeeSeeder::class,          // 16. Master Karyawan
        ]);
    }
}
