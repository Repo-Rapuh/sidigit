<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Menu;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class RoleController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize(('role.view'));
        $roles = Role::latest()->paginate(10);


        return view("admin.roles.index", compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('role.create');

        $menus = Menu::whereNull('parent_id')
            ->with('children.permissions', 'permissions')
            ->orderBy('order')
            ->get();

        return view('admin.roles.create', compact('menus'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('role.create');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'slug' => 'required|string|max:255|unique:roles,slug',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
            'menus' => 'nullable|array',
            'menus.*' => 'exists:menus,id'
        ]);

        // Buat Role baru
        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
        ]);

        // Hubungkan role dengan permission dan menu yang dipilih
        $role->permissions()->sync($validated['permissions'] ?? []);
        $role->menus()->sync($validated['menus'] ?? []);

        return redirect()->route('roles.index')->with('success', 'Role baru berhasil ditambahkan.');
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
    public function edit(Role $role)
    {
        $this->authorize('role.edit');

        // Ambil semua menu dengan relasi permission-nya, sama seperti di method create
        $menus = Menu::whereNull('parent_id')
            ->with('children.permissions', 'permissions')
            ->orderBy('order')
            ->get();


        return view('admin.roles.edit', compact('role', 'menus',));
    }


    public function update(Request $request, Role $role)
    {
        $this->authorize('role.edit');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'slug' => 'required|string|max:255|unique:roles,slug,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
            'menus' => 'nullable|array', // Pastikan ini ada
            'menus.*' => 'exists:menus,id' // Pastikan ini ada
        ]);

        $role->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);
        $role->menus()->sync($validated['menus'] ?? []); // Dan pastikan ini ada

        return redirect()->route('roles.index')->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        $this->authorize('role.delete');

        // Eloquent akan otomatis menghapus relasinya di tabel pivot (permission_role, menu_role, dll)
        $role->delete();

        // 3. Redirect kembali dengan pesan sukses
        return redirect()->route('roles.index')->with('success', 'Role berhasil dihapus.');
    }
}
