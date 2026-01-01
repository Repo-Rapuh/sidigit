<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Admin\UnitController;
use App\Livewire\Auth\ForgotPasswordPage;
use App\Livewire\Auth\LoginPage;
use App\Livewire\Auth\RegisterPage;
use App\Livewire\Auth\ResetPasswordPage;
use App\Livewire\Admin\Users\UsersIndex;
use App\Livewire\Admin\Roles\RolesIndex;
use App\Livewire\Admin\Roles\RolesCreate;
use App\Livewire\Admin\Roles\RolesEdit;
use App\Livewire\Admin\Permissions\PermissionsIndex;
use App\Livewire\Admin\Menus\MenusIndex;
use App\Livewire\Admin\AuditLogs\AuditLogsIndex;
use App\Livewire\Profile\Edit as ProfileEdit;
use App\Livewire\Admin\Suppliers\SuppliersIndex;
use App\Livewire\Admin\Suppliers\SuppliersCreate;
use App\Livewire\Admin\Suppliers\SuppliersEdit;
use App\Livewire\Admin\Warehouses\WarehousesIndex;
use App\Livewire\Admin\Warehouses\WarehousesCreate;
use App\Livewire\Admin\Warehouses\WarehousesEdit;
use App\Livewire\Admin\Employees\EmployeesIndex;
use App\Livewire\Admin\Employees\EmployeesCreate;
use App\Livewire\Admin\Employees\EmployeesEdit;
use App\Livewire\Admin\Customers\CustomersIndex;
use App\Livewire\Admin\Customers\CustomersCreate;
use App\Livewire\Admin\Customers\CustomersEdit;
use App\Livewire\Admin\BankAccounts\BankAccountsIndex;
use App\Livewire\Admin\Categories\CategoriesIndex;
use App\Livewire\Admin\Materials\MaterialsIndex;
use App\Livewire\Admin\Product\Index as ProductsIndex;
use App\Livewire\Admin\Product\Create as ProductsCreate;
use App\Livewire\Admin\Product\Edit as ProductsEdit;
use App\Livewire\Admin\Unit\Index as UnitIndex;

Route::get('/', function () {
    return redirect()->route('login');
});


Route::middleware('guest')->group(function () {
    Route::get('/login', LoginPage::class)->name('login');
    Route::get('/register', RegisterPage::class)->name('register');
    Route::get('/forgot-password', ForgotPasswordPage::class)->name('password.request');
    Route::get('/reset-password/{token}', ResetPasswordPage::class)->name('password.reset');
});



// Grup untuk Rute yang Membutuhkan Autentikasi
Route::middleware('auth')->group(function () {
    Route::get('/orders', function () {
        echo "test";
    })->name('orders.index');
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('bank-accounts', BankAccountsIndex::class)->name('bank-accounts.index');
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', CustomersIndex::class)->name('index');
        Route::get('/create', CustomersCreate::class)->name('create');
        Route::get('/{customer}/edit', CustomersEdit::class)->name('edit');
    });
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('/', EmployeesIndex::class)->name('index');
        Route::get('/create', EmployeesCreate::class)->name('create');
        Route::get('/{employee}/edit', EmployeesEdit::class)->name('edit');
    });


    // --- RUTE BARU UNTUK MANAJEMEN AKSES ---
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', UsersIndex::class)->name('index');
    });

    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', RolesIndex::class)->name('index');
        Route::get('/create', RolesCreate::class)->name('create');
        Route::get('/{role}/edit', RolesEdit::class)->name('edit');
    });

    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/', PermissionsIndex::class)->name('index');
    });

    Route::prefix('menus')->name('menus.')->group(function () {
        Route::get('/', MenusIndex::class)->name('index');
    });

    // -----------------------------------------

    // --- RUTE BARU UNTUK MANAJEMEN Barang ---
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', ProductsIndex::class)->name('index');
        Route::get('/create', ProductsCreate::class)->name('create');
        Route::get('/{product}/edit', ProductsEdit::class)->name('edit');
    });

    Route::get('units', UnitIndex::class)->name('units.index');

    Route::get('categories', CategoriesIndex::class)->name('categories.index');
    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', SuppliersIndex::class)->name('index');
        Route::get('/create', SuppliersCreate::class)->name('create');
        Route::get('/{supplier}/edit', SuppliersEdit::class)->name('edit');
    });
    Route::get('materials', MaterialsIndex::class)->name('materials.index');
    Route::prefix('warehouses')->name('warehouses.')->group(function () {
        Route::get('/', WarehousesIndex::class)->name('index');
        Route::get('/create', WarehousesCreate::class)->name('create');
        Route::get('/{warehouse}/edit', WarehousesEdit::class)->name('edit');
    });
    // -----------------------------------------

    // --- RUTE UNTUK PROFILE ---
    Route::get('/profile', ProfileEdit::class)->name('profile.edit');

    Route::get('audit-logs', AuditLogsIndex::class)->name('audit-logs.index');

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
