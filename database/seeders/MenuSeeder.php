<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Dashboard
        Menu::updateOrCreate(['route_name' => 'dashboard'], ['name' => 'Dashboard', 'icon' => 'bi bi-grid-fill', 'order' => 1]);

        // --- CUSTOMER ---
        Menu::updateOrCreate(['route_name' => 'customers.index'], ['name' => 'Customer', 'order' => 2]);

        // --- TRANSAKSI ---
        $transactions = Menu::updateOrCreate(['name' => 'Transaksi'], ['icon' => 'bi bi-receipt', 'order' => 3]);
        Menu::updateOrCreate(['route_name' => 'orders.index'], ['parent_id' => $transactions->id, 'name' => 'Order', 'order' => 1]);
        $productionMenu = Menu::updateOrCreate(
            ['name' => 'Produksi', 'parent_id' => $transactions->id],
            ['route_name' => 'productions.index', 'icon' => 'bi bi-kanban', 'order' => 2]
        );
        // Board desain + produksi kini digabung di /productions.
        Menu::whereIn('route_name', ['productions.desain', 'productions.produksi'])->delete();
        Menu::updateOrCreate(
            ['route_name' => 'productions.history'],
            ['parent_id' => $productionMenu->id, 'name' => 'Riwayat Produksi', 'order' => 1]
        );

        // --- MANAJEMEN PRODUK ---
        $productManagement = Menu::updateOrCreate(['name' => 'Manajemen Produk'], ['icon' => 'bi bi-box-seam-fill', 'order' => 4]);
        Menu::updateOrCreate(['route_name' => 'products.index'], ['parent_id' => $productManagement->id, 'name' => 'Produk', 'order' => 1]);
        Menu::updateOrCreate(['route_name' => 'materials.index'], ['parent_id' => $productManagement->id, 'name' => 'Bahan', 'order' => 2]);
        Menu::updateOrCreate(['route_name' => 'categories.index'], ['parent_id' => $productManagement->id, 'name' => 'Kategori', 'order' => 3]);
        Menu::updateOrCreate(['route_name' => 'units.index'], ['parent_id' => $productManagement->id, 'name' => 'Satuan', 'order' => 4]);

        // --- STOK ---
        $stockMenu = Menu::updateOrCreate(['name' => 'Stok'], ['icon' => 'bi bi-boxes', 'order' => 5]);
        Menu::updateOrCreate(
            ['parent_id' => $stockMenu->id, 'name' => 'Stok Masuk'],
            ['route_name' => 'stocks.in', 'order' => 1]
        );
        Menu::updateOrCreate(
            ['parent_id' => $stockMenu->id, 'name' => 'Stok Keluar'],
            ['route_name' => 'stocks.out', 'order' => 2]
        );
        Menu::updateOrCreate(
            ['parent_id' => $stockMenu->id, 'name' => 'Stok Opname'],
            ['route_name' => 'stocks.opname', 'order' => 3]
        );
        Menu::updateOrCreate(
            ['parent_id' => $stockMenu->id, 'name' => 'Saldo Stok'],
            ['route_name' => 'stocks.balances', 'order' => 4]
        );
        Menu::updateOrCreate(
            ['parent_id' => $stockMenu->id, 'name' => 'Reservasi Stok'],
            ['route_name' => 'stocks.reservations', 'order' => 5]
        );

        // --- PENGELUARAN ---
        $expenseMenu = Menu::updateOrCreate(['name' => 'Pengeluaran'], ['icon' => 'bi bi-wallet2', 'order' => 6]);
        Menu::updateOrCreate(['route_name' => 'expenses.materials.index'], ['parent_id' => $expenseMenu->id, 'name' => 'Expense Bahan', 'order' => 1]);
        Menu::updateOrCreate(['route_name' => 'expenses.general.index'], ['parent_id' => $expenseMenu->id, 'name' => 'Expense Umum', 'order' => 2]);

        // --- LAPORAN ---
        $reportMenu = Menu::updateOrCreate(['name' => 'Laporan'], ['icon' => 'bi bi-graph-up', 'order' => 7]);
        Menu::updateOrCreate(['route_name' => 'reports.sales'], ['parent_id' => $reportMenu->id, 'name' => 'Laporan Penjualan', 'order' => 1]);
        Menu::updateOrCreate(['route_name' => 'reports.expenses'], ['parent_id' => $reportMenu->id, 'name' => 'Laporan Pengeluaran', 'order' => 2]);
        Menu::updateOrCreate(['route_name' => 'reports.production'], ['parent_id' => $reportMenu->id, 'name' => 'Laporan Produksi', 'order' => 3]);
        Menu::updateOrCreate(['route_name' => 'reports.financial'], ['parent_id' => $reportMenu->id, 'name' => 'Laporan Keuangan', 'order' => 4]);
        Menu::updateOrCreate(['route_name' => 'reports.branches'], ['parent_id' => $reportMenu->id, 'name' => 'Laporan Per Cabang', 'order' => 5]);

        // --- AKUNTANSI ---
        $accountingMenu = Menu::updateOrCreate(['name' => 'Akuntansi'], ['icon' => 'bi bi-journal-text', 'order' => 8]);
        Menu::updateOrCreate(['route_name' => 'accounting.overview'], ['parent_id' => $accountingMenu->id, 'name' => 'Dashboard Akuntansi', 'order' => 1]);
        Menu::updateOrCreate(['route_name' => 'cashflows.index'], ['parent_id' => $accountingMenu->id, 'name' => 'Arus Kas', 'order' => 2]);
        Menu::updateOrCreate(['route_name' => 'accounts.index'], ['parent_id' => $accountingMenu->id, 'name' => 'Chart of Accounts', 'order' => 3]);
        Menu::updateOrCreate(['route_name' => 'journals.index'], ['parent_id' => $accountingMenu->id, 'name' => 'Jurnal Umum', 'order' => 4]);

        // --- MASTER DATA ---
        $masterData = Menu::updateOrCreate(['name' => 'Master'], ['icon' => 'bi bi-layers', 'order' => 9]);
        Menu::updateOrCreate(['route_name' => 'suppliers.index'], ['parent_id' => $masterData->id, 'name' => 'Supplier', 'order' => 2]);
        Menu::updateOrCreate(['route_name' => 'warehouses.index'], ['parent_id' => $masterData->id, 'name' => 'Gudang', 'order' => 3]);
        Menu::updateOrCreate(['route_name' => 'finishes.index'], ['parent_id' => $masterData->id, 'name' => 'Finishing', 'order' => 4]);
        Menu::updateOrCreate(['route_name' => 'employees.index'], ['parent_id' => $masterData->id, 'name' => 'Karyawan', 'order' => 5]);
        Menu::updateOrCreate(['route_name' => 'attendances.index'], ['parent_id' => $masterData->id, 'name' => 'Absensi', 'order' => 6]);
        Menu::updateOrCreate(['route_name' => 'employee-loans.index'], ['parent_id' => $masterData->id, 'name' => 'Kasbon', 'order' => 7]);


        // --- SETTINGS ---
        $settingData = Menu::updateOrCreate(['name' => 'Settings'], ['icon' => 'bi bi-layers', 'order' => 10]);
        Menu::updateOrCreate(['route_name' => 'bank-accounts.index'], ['parent_id' => $settingData->id, 'name' => 'Rekening Bank', 'order' => 5]);
        Menu::updateOrCreate(['route_name' => 'branches.index'], ['parent_id' => $settingData->id, 'name' => 'Cabang', 'order' => 9]);
        Menu::updateOrCreate(['route_name' => 'file-manager.index'], ['parent_id' => $settingData->id, 'name' => 'File Manager', 'order' => 10]);


        // --- RBAC ---
        $rbac = Menu::updateOrCreate(['name' => 'RBAC'], ['icon' => 'bi bi-shield-shaded', 'order' => 11]);
        Menu::updateOrCreate(['route_name' => 'users.index'], ['parent_id' => $rbac->id, 'name' => 'Manajemen User', 'order' => 1]);
        Menu::updateOrCreate(['route_name' => 'roles.index'], ['parent_id' => $rbac->id, 'name' => 'Manajemen Role', 'order' => 2]);
        Menu::updateOrCreate(['route_name' => 'permissions.index'], ['parent_id' => $rbac->id, 'name' => 'Manajemen Permission', 'order' => 3]);
        Menu::updateOrCreate(['route_name' => 'menus.index'], ['parent_id' => $rbac->id, 'name' => 'Manajemen Menu', 'order' => 4]);

        // --- LAINNYA ---
        Menu::updateOrCreate(['route_name' => 'audit-logs.index'], ['name' => 'Audit Logs', 'icon' => 'bi bi-clipboard-data', 'order' => 12]);
    }
}
