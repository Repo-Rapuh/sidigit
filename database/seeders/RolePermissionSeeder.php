<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
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

        // --- Hak Akses Menu ---
        $allMenus = Menu::pluck('id');
        if ($superadminRole) {
            $superadminRole->menus()->sync($allMenus);
        }
        if ($ownerRole) {
            $ownerRole->menus()->sync($allMenus);
        }

        if ($kasirRole) {
            $kasirMenu = Menu::whereIn('name', [
                'Dashboard',
                'Customer',
                'Transaksi',
                'Order',
                'Produksi',
                'Riwayat Produksi',
                'Manajemen Produk',
                'Produk',
                'Bahan',
                'Kategori',
                'Stok',
                'Stok Masuk',
                'Stok Keluar',
                'Stok Opname',
                'Reservasi Stok',
                'Pengeluaran',
                'Expense Bahan',
                'Expense Umum',
                'Laporan',
                'Laporan Penjualan',
                'Laporan Pengeluaran',
                'Laporan Produksi',
                'Laporan Keuangan',
                'Laporan Per Cabang',
                'Master',
                'Supplier',
                'Gudang',
                'Finishing',
            ])->pluck('id');

            $kasirRole->menus()->sync($kasirMenu);
        }

        $productionAndStockMenus = Menu::whereIn('name', [
            'Transaksi',
            'Produksi',
            'Stok',
            'Stok Masuk',
            'Stok Keluar',
            'Stok Opname',
            'Saldo Stok',
            'Reservasi Stok',
        ])->pluck('id');

        if ($desainerRole) {
            $desainerRole->menus()->sync($productionAndStockMenus);
        }

        if ($operatorRole) {
            $operatorRole->menus()->sync($productionAndStockMenus);
        }

        // --- Hak Akses Permission ---
        $allPermissions = Permission::pluck('id');
        if ($superadminRole) {
            $superadminRole->permissions()->sync($allPermissions);
        }
        if ($ownerRole) {
            $ownerRole->permissions()->sync($allPermissions);
        }

        if ($kasirRole) {
            $kasirPermissions = Permission::whereIn('slug', [
                'customer.view', 'customer.create', 'customer.edit', 'customer.delete',
                'order.view', 'order.create', 'order.edit',
                'production.view', 'production.edit', 'production.assign', 'production.qc',
                'product.view', 'product.create', 'product.edit', 'product.export',
                'category.view', 'category.export',
                'material.view', 'material.create', 'material.edit',
                'stock-in.view', 'stock-in.create', 'stock-in.edit',
                'stock-out.view', 'stock-out.create', 'stock-out.edit',
                'stock-opname.view', 'stock-opname.create', 'stock-opname.edit',
                'stock-balance.view',
                'stock-reservation.view',
                'expense-material.view', 'expense-material.create', 'expense-material.edit',
                'expense-general.view', 'expense-general.create', 'expense-general.edit',
                'supplier.view', 'supplier.create', 'supplier.edit', 'supplier.delete',
                'warehouse.view', 'warehouse.create', 'warehouse.edit', 'warehouse.delete',
                'finish.view', 'finish.create', 'finish.edit', 'finish.delete',
                'report.sales.view', 'report.expense.view', 'report.production.view', 'report.finance.view', 'report.branch.view',
            ])->pluck('id');

            $kasirRole->permissions()->sync($kasirPermissions);
        }

        $stockAllPermissions = [
            'stock-in.view', 'stock-in.create', 'stock-in.edit', 'stock-in.delete',
            'stock-out.view', 'stock-out.create', 'stock-out.edit', 'stock-out.delete',
            'stock-opname.view', 'stock-opname.create', 'stock-opname.edit', 'stock-opname.delete',
            'stock-balance.view',
            'stock-reservation.view',
        ];

        if ($desainerRole) {
            $desainerPermissions = Permission::whereIn('slug', array_merge([
                'production.view',
                'production.edit',
            ], $stockAllPermissions))->pluck('id');

            $desainerRole->permissions()->sync($desainerPermissions);
        }

        if ($operatorRole) {
            $operatorPermissions = Permission::whereIn('slug', array_merge([
                'production.view',
                'production.edit',
                'production.qc',
            ], $stockAllPermissions))->pluck('id');

            $operatorRole->permissions()->sync($operatorPermissions);
        }
    }
}
