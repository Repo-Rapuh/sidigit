<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu; 
use Illuminate\Http\Request;
use App\Helpers\IconHelper;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MenuController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 1. Otorisasi: Pastikan user punya izin 'menu.view'
        $this->authorize('menu.view');


        // 2. Ambil semua data menu dengan relasi parent-nya untuk efisiensi
        $menus = Menu::with('parent')->latest()->paginate(10);

        // 3. Kirim data ke view
        return view('admin.menu.index', compact('menus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('menu.create');

        $parentMenus = Menu::whereNull('route_name')->orWhere('route_name', '=', '')->get();
        // Siapkan daftar ikon untuk dikirim ke view
        $icons = IconHelper::getIcons();

        return view('admin.menu.create', compact('parentMenus', 'icons'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Otorisasi: Pastikan user punya izin 'menu.create'
        $this->authorize('menu.create');

        // 2. Validasi input dari form
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:menus,id',
            // Pastikan route_name unik jika diisi, kecuali untuk menu yang sama (berguna saat update)
            'route_name' => 'nullable|string|max:255|unique:menus,route_name',
            'icon' => 'nullable|string|max:255',
            'order' => 'required|integer',
        ], [
            // Pesan error kustom (opsional, tapi bagus)
            'name.required' => 'Nama menu tidak boleh kosong.',
            'route_name.unique' => 'Route name ini sudah digunakan menu lain.',
            'order.required' => 'Urutan tampil tidak boleh kosong.',
        ]);

        // 3. Simpan data baru ke database
        Menu::create($validated);

        // 4. Redirect kembali ke halaman index dengan pesan sukses (flash message)
        return redirect()->route('menus.index')->with('success', 'Menu baru berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Menu $menu)
    {

        // 1. Otorisasi: Pastikan user punya izin 'menu.edit'
        $this->authorize('menu.edit');

        // 2. Ambil semua menu untuk pilihan "Induk Menu"
        $parentMenus = Menu::all();

        // 3. Ambil daftar ikon
        $icons = IconHelper::getIcons(); // Pastikan method ini ada dari langkah sebelumnya

        // 4. Kirim semua data yang dibutuhkan ke view
        return view('admin.menu.edit', compact('menu', 'parentMenus', 'icons'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Menu $menu)
    {
        // 1. Otorisasi: Pastikan user punya izin 'menu.edit'
        $this->authorize('menu.edit');

        // 2. Validasi input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:menus,id',
            // Validasi unik, tapi abaikan untuk menu saat ini
            'route_name' => 'nullable|string|max:255|unique:menus,route_name,' . $menu->id,
            'icon' => 'nullable|string|max:255',
            'order' => 'required|integer',
        ]);

        // 3. Update data di database
        $menu->update($validated);

        // 4. Redirect ke halaman index dengan pesan sukses
        return redirect()->route('menus.index')->with('success', 'Menu berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Menu $menu)
    {
        // 1. Otorisasi: Pastikan user punya izin 'menu.delete'
        $this->authorize('menu.delete');

        // 2. Hapus data dari database
        $menu->delete();

        // 3. Redirect kembali ke halaman index dengan pesan sukses
        return redirect()->route('menus.index')->with('success', 'Menu berhasil dihapus.');
    }
}
