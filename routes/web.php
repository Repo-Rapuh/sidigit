<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BankAccountController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Livewire\Auth\ForgotPasswordPage;
use App\Livewire\Auth\LoginPage;
use App\Livewire\Auth\RegisterPage;
use App\Livewire\Auth\ResetPasswordPage;
use App\Livewire\Admin\Users\UsersIndex;
use App\Livewire\Admin\Users\UsersCreate;
use App\Livewire\Admin\Users\UsersEdit;
use App\Livewire\Admin\Roles\RolesIndex;
use App\Livewire\Admin\Roles\RolesCreate;
use App\Livewire\Admin\Roles\RolesEdit;
use App\Livewire\Admin\Permissions\PermissionsIndex;
use App\Livewire\Admin\Menus\MenusIndex;
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

    Route::resource('bank-accounts', BankAccountController::class)->except('show');
    Route::resource('customers', CustomerController::class)->except('show');
    Route::resource('employees', EmployeeController::class)->except('show');


    // --- RUTE BARU UNTUK MANAJEMEN AKSES ---
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', UsersIndex::class)->name('index');
        Route::get('/create', UsersCreate::class)->name('create');
        Route::get('/{user}/edit', UsersEdit::class)->name('edit');
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

    Route::resource('categories', CategoryController::class);
    Route::resource('suppliers', SupplierController::class)->except('show');
    Route::resource('materials', MaterialController::class)->except('show');
    Route::resource('warehouses', WarehouseController::class)->except('show');
    // -----------------------------------------

    // --- RUTE UNTUK PROFILE ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('audit-logs', [ActivityLogController::class, 'index'])->name('audit-logs.index');

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
