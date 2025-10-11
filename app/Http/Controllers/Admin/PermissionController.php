<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\Menu;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PermissionController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize("permission.view");
        $permissions = Permission::with('menu')->latest()->paginate(10);

        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {

        $this->authorize("permission.create");
        $menus = Menu::whereNotNull('route_name')->orWhere('route_name', '!=', '')->get();
        return view('admin.permissions.create', compact('menus'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $this->authorize("permission.create");
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:permissions,slug',
            'menu_id' => 'required|exists:menus,id',
        ]);

        Permission::create($validated);

        return redirect()->route('permissions.index')->with('success', 'Permission created successfully.');
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
    public function edit(Permission $permission)
    {

        $this->authorize("permission.edit");
        $menus = Menu::all();

        return view('admin.permissions.edit', compact('permission', 'menus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {

        $this->authorize("permission.edit");
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:permissions,slug,' . $permission->id,
            'menu_id' => 'required|exists:menus,id',
        ]);

        $permission->update($validated);

        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {

        $this->authorize("permission.delete");
        $permission->delete();
        return redirect()->route('permissions.index')->with('success','Permission deleted successfully.');
    }
}
