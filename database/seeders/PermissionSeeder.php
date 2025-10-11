<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil Menu Induk untuk pengelompokan
        $menuRBAC = Menu::where('name', 'RBAC')->first();
        $menuProduk = Menu::where('name', 'Produk')->first();
        $menuKategori = Menu::where('name', 'Kategori')->first();

        // Daftar Izin (Permissions)
        $permissions = [
            // RBAC
            'role.view' => ['name' => 'Lihat Role', 'menu_id' => $menuRBAC->id],
            'role.create' => ['name' => 'Tambah Role', 'menu_id' => $menuRBAC->id],
            'role.edit' => ['name' => 'Edit Role', 'menu_id' => $menuRBAC->id],
            'role.delete' => ['name' => 'Hapus Role', 'menu_id' => $menuRBAC->id],

            // Produk
            'product.view' => ['name' => 'Lihat Produk', 'menu_id' => $menuProduk->id],
            'product.create' => ['name' => 'Tambah Produk', 'menu_id' => $menuProduk->id],
            'product.edit' => ['name' => 'Edit Produk', 'menu_id' => $menuProduk->id],
            'product.delete' => ['name' => 'Hapus Produk', 'menu_id' => $menuProduk->id],
            'product.export' => ['name' => 'Export Produk', 'menu_id' => $menuProduk->id],

            // Kategori
            'category.view' => ['name' => 'Lihat Kategori', 'menu_id' => $menuKategori->id],
            'category.create' => ['name' => 'Tambah Kategori', 'menu_id' => $menuKategori->id],
            'category.edit' => ['name' => 'Edit Kategori', 'menu_id' => $menuKategori->id],
            'category.delete' => ['name' => 'Hapus Kategori', 'menu_id' => $menuKategori->id],
            'category.export' => ['name' => 'Export Kategori', 'menu_id' => $menuKategori->id],
        ];

        // Buat Permissions
        foreach ($permissions as $slug => $details) {
            Permission::updateOrCreate(['slug' => $slug], ['name' => $details['name'], 'menu_id' => $details['menu_id']]);
        }
    }
}
