<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $menuByName = Menu::pluck('id', 'name');
        $menuId = fn (string $name) => $menuByName[$name] ?? null;

        // Daftar Izin (Permissions)
        $permissions = [
            // RBAC
            'role.view' => ['name' => 'Lihat Role', 'menu_id' => $menuId('RBAC')],
            'role.create' => ['name' => 'Tambah Role', 'menu_id' => $menuId('RBAC')],
            'role.edit' => ['name' => 'Edit Role', 'menu_id' => $menuId('RBAC')],
            'role.delete' => ['name' => 'Hapus Role', 'menu_id' => $menuId('RBAC')],

            // Produk
            'product.view' => ['name' => 'Lihat Produk', 'menu_id' => $menuId('Produk')],
            'product.create' => ['name' => 'Tambah Produk', 'menu_id' => $menuId('Produk')],
            'product.edit' => ['name' => 'Edit Produk', 'menu_id' => $menuId('Produk')],
            'product.delete' => ['name' => 'Hapus Produk', 'menu_id' => $menuId('Produk')],
            'product.export' => ['name' => 'Export Produk', 'menu_id' => $menuId('Produk')],

            // Kategori
            'category.view' => ['name' => 'Lihat Kategori', 'menu_id' => $menuId('Kategori')],
            'category.create' => ['name' => 'Tambah Kategori', 'menu_id' => $menuId('Kategori')],
            'category.edit' => ['name' => 'Edit Kategori', 'menu_id' => $menuId('Kategori')],
            'category.delete' => ['name' => 'Hapus Kategori', 'menu_id' => $menuId('Kategori')],
            'category.export' => ['name' => 'Export Kategori', 'menu_id' => $menuId('Kategori')],

            // Menu
            'menu.view' => ['name' => 'Lihat Menu', 'menu_id' => $menuId('RBAC')],
            'menu.create' => ['name' => 'Tambah Menu', 'menu_id' => $menuId('RBAC')],
            'menu.edit' => ['name' => 'Edit Menu', 'menu_id' => $menuId('RBAC')],
            'menu.delete' => ['name' => 'Hapus Menu', 'menu_id' => $menuId('RBAC')],

            // Permission
            'permission.view' => ['name' => 'Lihat Permission', 'menu_id' => $menuId('RBAC')],
            'permission.create' => ['name' => 'Tambah Permission', 'menu_id' => $menuId('RBAC')],
            'permission.edit' => ['name' => 'Edit Permission', 'menu_id' => $menuId('RBAC')],

            // Unit
            'unit.view' => ['name' => 'Lihat Satuan', 'menu_id' => $menuId('Satuan')],
            'unit.create' => ['name' => 'Tambah Satuan', 'menu_id' => $menuId('Satuan')],
            'unit.edit' => ['name' => 'Edit Satuan', 'menu_id' => $menuId('Satuan')],
            'unit.delete' => ['name' => 'Hapus Satuan', 'menu_id' => $menuId('Satuan')],

            // Material
            'material.view' => ['name' => 'Lihat Bahan', 'menu_id' => $menuId('Bahan')],
            'material.create' => ['name' => 'Tambah Bahan', 'menu_id' => $menuId('Bahan')],
            'material.edit' => ['name' => 'Edit Bahan', 'menu_id' => $menuId('Bahan')],
            'material.delete' => ['name' => 'Hapus Bahan', 'menu_id' => $menuId('Bahan')],

            // Supplier
            'supplier.view' => ['name' => 'Lihat Supplier', 'menu_id' => $menuId('Supplier')],
            'supplier.create' => ['name' => 'Tambah Supplier', 'menu_id' => $menuId('Supplier')],
            'supplier.edit' => ['name' => 'Edit Supplier', 'menu_id' => $menuId('Supplier')],
            'supplier.delete' => ['name' => 'Hapus Supplier', 'menu_id' => $menuId('Supplier')],

            // Warehouse
            'warehouse.view' => ['name' => 'Lihat Gudang', 'menu_id' => $menuId('Gudang')],
            'warehouse.create' => ['name' => 'Tambah Gudang', 'menu_id' => $menuId('Gudang')],
            'warehouse.edit' => ['name' => 'Edit Gudang', 'menu_id' => $menuId('Gudang')],
            'warehouse.delete' => ['name' => 'Hapus Gudang', 'menu_id' => $menuId('Gudang')],

            // Customer
            'customer.view' => ['name' => 'Lihat Customer', 'menu_id' => $menuId('Customer')],
            'customer.create' => ['name' => 'Tambah Customer', 'menu_id' => $menuId('Customer')],
            'customer.edit' => ['name' => 'Edit Customer', 'menu_id' => $menuId('Customer')],
            'customer.delete' => ['name' => 'Hapus Customer', 'menu_id' => $menuId('Customer')],

            // Employee
            'employee.view' => ['name' => 'Lihat Karyawan', 'menu_id' => $menuId('Karyawan')],
            'employee.create' => ['name' => 'Tambah Karyawan', 'menu_id' => $menuId('Karyawan')],
            'employee.edit' => ['name' => 'Edit Karyawan', 'menu_id' => $menuId('Karyawan')],

            // Bank Account
            'bank-account.view' => ['name' => 'Lihat Rekening Bank', 'menu_id' => $menuId('Rekening Bank')],
            'bank-account.create' => ['name' => 'Tambah Rekening Bank', 'menu_id' => $menuId('Rekening Bank')],
            'bank-account.edit' => ['name' => 'Edit Rekening Bank', 'menu_id' => $menuId('Rekening Bank')],
            'bank-account.delete' => ['name' => 'Hapus Rekening Bank', 'menu_id' => $menuId('Rekening Bank')],

            // File Manager
            'file-manager.view' => ['name' => 'Lihat File Manager', 'menu_id' => $menuId('File Manager')],
            'file-manager.delete' => ['name' => 'Hapus File Manager', 'menu_id' => $menuId('File Manager')],

            // Audit Log
            'audit-log.view' => ['name' => 'Lihat Audit Log', 'menu_id' => $menuId('Audit Logs')],

            // Users
            'users.view' => ['name' => 'Lihat Pengguna', 'menu_id' => $menuId('Manajemen User')],
            'users.create' => ['name' => 'Tambah Pengguna', 'menu_id' => $menuId('Manajemen User')],
            'users.edit' => ['name' => 'Edit Pengguna', 'menu_id' => $menuId('Manajemen User')],

            // Orders
            'order.view' => ['name' => 'Lihat Order', 'menu_id' => $menuId('Order')],
            'order.create' => ['name' => 'Tambah Order', 'menu_id' => $menuId('Order')],
            'order.edit' => ['name' => 'Edit Order', 'menu_id' => $menuId('Order')],
            'order.delete' => ['name' => 'Hapus Order', 'menu_id' => $menuId('Order')],
            'workflow.override.status' => ['name' => 'Override Workflow Status', 'menu_id' => $menuId('Order')],
            'workflow.override.actor' => ['name' => 'Override PIC Workflow', 'menu_id' => $menuId('Order')],
            'workflow.override.locked-order' => ['name' => 'Override Edit Order Terkunci', 'menu_id' => $menuId('Order')],

            // Produksi
            'production.view' => ['name' => 'Lihat Produksi', 'menu_id' => $menuId('Produksi')],
            'production.edit' => ['name' => 'Update Status Produksi', 'menu_id' => $menuId('Produksi')],
            'production.assign' => ['name' => 'Assign Role Produksi', 'menu_id' => $menuId('Produksi')],
            'production.qc' => ['name' => 'QC Produksi', 'menu_id' => $menuId('Produksi')],

            // Finishing
            'finish.view' => ['name' => 'Lihat Finishing', 'menu_id' => $menuId('Finishing')],
            'finish.create' => ['name' => 'Tambah Finishing', 'menu_id' => $menuId('Finishing')],
            'finish.edit' => ['name' => 'Edit Finishing', 'menu_id' => $menuId('Finishing')],
            'finish.delete' => ['name' => 'Hapus Finishing', 'menu_id' => $menuId('Finishing')],

            // Stok Masuk
            'stock-in.view' => ['name' => 'Lihat Stok Masuk', 'menu_id' => $menuId('Stok Masuk')],
            'stock-in.create' => ['name' => 'Tambah Stok Masuk', 'menu_id' => $menuId('Stok Masuk')],
            'stock-in.edit' => ['name' => 'Edit Stok Masuk', 'menu_id' => $menuId('Stok Masuk')],
            'stock-in.delete' => ['name' => 'Hapus Stok Masuk', 'menu_id' => $menuId('Stok Masuk')],

            // Stok Keluar
            'stock-out.view' => ['name' => 'Lihat Stok Keluar', 'menu_id' => $menuId('Stok Keluar')],
            'stock-out.create' => ['name' => 'Tambah Stok Keluar', 'menu_id' => $menuId('Stok Keluar')],
            'stock-out.edit' => ['name' => 'Edit Stok Keluar', 'menu_id' => $menuId('Stok Keluar')],
            'stock-out.delete' => ['name' => 'Hapus Stok Keluar', 'menu_id' => $menuId('Stok Keluar')],

            // Stok Opname
            'stock-opname.view' => ['name' => 'Lihat Stok Opname', 'menu_id' => $menuId('Stok Opname')],
            'stock-opname.create' => ['name' => 'Tambah Stok Opname', 'menu_id' => $menuId('Stok Opname')],
            'stock-opname.edit' => ['name' => 'Edit Stok Opname', 'menu_id' => $menuId('Stok Opname')],
            'stock-opname.delete' => ['name' => 'Hapus Stok Opname', 'menu_id' => $menuId('Stok Opname')],

            // Saldo Stok
            'stock-balance.view' => ['name' => 'Lihat Saldo Stok', 'menu_id' => $menuId('Saldo Stok')],

            // Reservasi Stok
            'stock-reservation.view' => ['name' => 'Lihat Reservasi Stok', 'menu_id' => $menuId('Reservasi Stok')],

            // Expense Bahan
            'expense-material.view' => ['name' => 'Lihat Expense Bahan', 'menu_id' => $menuId('Expense Bahan')],
            'expense-material.create' => ['name' => 'Tambah Expense Bahan', 'menu_id' => $menuId('Expense Bahan')],
            'expense-material.edit' => ['name' => 'Edit Expense Bahan', 'menu_id' => $menuId('Expense Bahan')],
            'expense-material.delete' => ['name' => 'Hapus Expense Bahan', 'menu_id' => $menuId('Expense Bahan')],

            // Expense Umum
            'expense-general.view' => ['name' => 'Lihat Expense Umum', 'menu_id' => $menuId('Expense Umum')],
            'expense-general.create' => ['name' => 'Tambah Expense Umum', 'menu_id' => $menuId('Expense Umum')],
            'expense-general.edit' => ['name' => 'Edit Expense Umum', 'menu_id' => $menuId('Expense Umum')],
            'expense-general.delete' => ['name' => 'Hapus Expense Umum', 'menu_id' => $menuId('Expense Umum')],

            // Absensi
            'attendance.view' => ['name' => 'Lihat Absensi', 'menu_id' => $menuId('Absensi')],
            'attendance.create' => ['name' => 'Tambah Absensi', 'menu_id' => $menuId('Absensi')],
            'attendance.edit' => ['name' => 'Edit Absensi', 'menu_id' => $menuId('Absensi')],
            'attendance.delete' => ['name' => 'Hapus Absensi', 'menu_id' => $menuId('Absensi')],

            // Kasbon
            'employee-loan.view' => ['name' => 'Lihat Kasbon', 'menu_id' => $menuId('Kasbon')],
            'employee-loan.create' => ['name' => 'Tambah Kasbon', 'menu_id' => $menuId('Kasbon')],
            'employee-loan.edit' => ['name' => 'Edit Kasbon', 'menu_id' => $menuId('Kasbon')],
            'employee-loan.delete' => ['name' => 'Hapus Kasbon', 'menu_id' => $menuId('Kasbon')],

            // Cabang
            'branch.view' => ['name' => 'Lihat Cabang', 'menu_id' => $menuId('Cabang')],
            'branch.create' => ['name' => 'Tambah Cabang', 'menu_id' => $menuId('Cabang')],
            'branch.edit' => ['name' => 'Edit Cabang', 'menu_id' => $menuId('Cabang')],
            'branch.delete' => ['name' => 'Hapus Cabang', 'menu_id' => $menuId('Cabang')],

            // Akuntansi - COA
            'accounting-overview.view' => ['name' => 'Lihat Dashboard Akuntansi', 'menu_id' => $menuId('Dashboard Akuntansi')],
            'cashflow.view' => ['name' => 'Lihat Arus Kas', 'menu_id' => $menuId('Arus Kas')],
            'account.view' => ['name' => 'Lihat Chart of Accounts', 'menu_id' => $menuId('Chart of Accounts')],
            'account.create' => ['name' => 'Tambah Akun COA', 'menu_id' => $menuId('Chart of Accounts')],
            'account.edit' => ['name' => 'Edit Akun COA', 'menu_id' => $menuId('Chart of Accounts')],
            'account.delete' => ['name' => 'Hapus Akun COA', 'menu_id' => $menuId('Chart of Accounts')],

            // Akuntansi - Jurnal
            'journal.view' => ['name' => 'Lihat Jurnal Umum', 'menu_id' => $menuId('Jurnal Umum')],
            'journal.create' => ['name' => 'Tambah Jurnal Umum', 'menu_id' => $menuId('Jurnal Umum')],

            // Laporan
            'report.sales.view' => ['name' => 'Lihat Laporan Penjualan', 'menu_id' => $menuId('Laporan Penjualan')],
            'report.expense.view' => ['name' => 'Lihat Laporan Pengeluaran', 'menu_id' => $menuId('Laporan Pengeluaran')],
            'report.production.view' => ['name' => 'Lihat Laporan Produksi', 'menu_id' => $menuId('Laporan Produksi')],
            'report.finance.view' => ['name' => 'Lihat Laporan Keuangan', 'menu_id' => $menuId('Laporan Keuangan')],
            'report.branch.view' => ['name' => 'Lihat Laporan Per Cabang', 'menu_id' => $menuId('Laporan Per Cabang')],
        ];

        // Buat Permissions
        foreach ($permissions as $slug => $details) {
            Permission::updateOrCreate(
                ['slug' => $slug],
                ['name' => $details['name'], 'menu_id' => $details['menu_id']]
            );
        }
    }
}
