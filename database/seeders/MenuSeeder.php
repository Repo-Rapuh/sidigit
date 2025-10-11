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

        // --- RBAC ---
        $rbac = Menu::updateOrCreate(['name' => 'RBAC'], ['icon' => 'bi bi-shield-shaded', 'order' => 2]);
        Menu::updateOrCreate(['route_name' => 'roles.index'], ['parent_id' => $rbac->id, 'name' => 'Manajemen Role', 'order' => 1]);
        Menu::updateOrCreate(['route_name' => 'permissions.index'], ['parent_id' => $rbac->id, 'name' => 'Manajemen Permission', 'order' => 2]);
        Menu::updateOrCreate(['route_name' => 'menus.index'], ['parent_id' => $rbac->id, 'name' => 'Manajemen Menu', 'order' => 3]);

        // --- MANAJEMEN BARANG ---
        $itemManagement = Menu::updateOrCreate(['name' => 'Manajemen Barang'], ['icon' => 'bi bi-box-seam-fill', 'order' => 3]);
        Menu::updateOrCreate(['route_name' => 'products.index'], ['parent_id' => $itemManagement->id, 'name' => 'Produk', 'order' => 1]);
        Menu::updateOrCreate(['route_name' => 'categories.index'], ['parent_id' => $itemManagement->id, 'name' => 'Kategori', 'order' => 2]);
    }
}
