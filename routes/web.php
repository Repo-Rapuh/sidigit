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
use App\Livewire\Admin\Branches\BranchesIndex;
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
use App\Livewire\Admin\Stocks\StockInIndex as StockInIndex;
use App\Livewire\Admin\Stocks\StockOutIndex as StockOutIndex;
use App\Livewire\Admin\Stocks\StockOpnameIndex as StockOpnameIndex;
use App\Livewire\Admin\Stocks\BalanceIndex as StockBalanceIndex;
use App\Livewire\Admin\Expenses\MaterialIndex as MaterialExpenseIndex;
use App\Livewire\Admin\Expenses\GeneralIndex as GeneralExpenseIndex;
use App\Livewire\Admin\Attendances\Index as AttendancesIndex;
use App\Livewire\Admin\EmployeeLoans\Index as EmployeeLoansIndex;
use App\Livewire\Admin\Reports\SalesReport as SalesReport;
use App\Livewire\Admin\Reports\ExpenseReport as ExpenseReport;
use App\Livewire\Admin\Reports\BranchReport as BranchReport;
use App\Livewire\Admin\Reports\ProductionReport as ProductionReport;
use App\Livewire\Admin\Reports\FinancialReport as FinancialReport;
use App\Livewire\Admin\FileManager\Index as FileManagerIndex;
use App\Livewire\Admin\Orders\AddPayment as OrderAddPayment;
use App\Livewire\Admin\Productions\Index as ProductionsIndex;
use App\Livewire\Admin\Productions\HistoryIndex as ProductionsHistoryIndex;
use App\Livewire\Admin\Accounting\Overview\Index as AccountingOverviewIndex;
use App\Livewire\Admin\Accounting\Accounts\Index as AccountingAccountsIndex;
use App\Livewire\Admin\Accounting\Journals\Index as AccountingJournalsIndex;
use App\Livewire\Admin\Accounting\Cashflows\Index as AccountingCashflowsIndex;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FileManagerController;
use App\Http\Controllers\Tracking\OrderTrackingController;

Route::get('/', function () {
    return redirect()->route('login');
});


Route::middleware('guest')->group(function () {
    Route::get('/login', LoginPage::class)->name('login');
    Route::get('/register', RegisterPage::class)->name('register');
    Route::get('/forgot-password', ForgotPasswordPage::class)->name('password.request');
    Route::get('/reset-password/{token}', ResetPasswordPage::class)->name('password.reset');
});

Route::get('/track/order/{id_order_encrypted}', [OrderTrackingController::class, 'show'])
    ->where('id_order_encrypted', '[A-Za-z0-9\\-_]+')
    ->name('orders.track.public');



// Grup untuk Rute yang Membutuhkan Autentikasi
    Route::middleware(['auth', 'route.permission'])->group(function () {
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', \App\Livewire\Admin\Orders\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Admin\Orders\Create::class)->name('create');
            Route::get('/{order}/payments', OrderAddPayment::class)->name('payments.create');
            Route::get('/{order}/edit', \App\Livewire\Admin\Orders\Edit::class)->name('edit');
            Route::get('/{order}/invoice', [\App\Http\Controllers\Admin\OrderInvoiceController::class, 'show'])->name('invoice');
            Route::get('/{order}/invoice/pdf', [\App\Http\Controllers\Admin\OrderInvoiceController::class, 'pdf'])->name('invoice.pdf');
            Route::get('/{order}/quotation', [\App\Http\Controllers\Admin\OrderInvoiceController::class, 'quotation'])->name('quotation');
            Route::get('/{order}/quotation/pdf', [\App\Http\Controllers\Admin\OrderInvoiceController::class, 'quotationPdf'])->name('quotation.pdf');
            Route::get('/{order}/receipt', [\App\Http\Controllers\Admin\OrderReceiptController::class, 'show'])->name('receipt');
            Route::get('/trashed', \App\Livewire\Admin\Orders\Trashed::class)->name('trashed');
        });

    Route::prefix('productions')->name('productions.')->group(function () {
        Route::get('/', ProductionsIndex::class)->name('index');
        Route::get('/desain', fn () => redirect()->route('productions.index'))->name('desain');
        Route::get('/produksi', fn () => redirect()->route('productions.index'))->name('produksi');
        Route::get('/history', ProductionsHistoryIndex::class)->name('history');
    });

    Route::prefix('stocks')->group(function () {
        Route::get('/in', StockInIndex::class)->name('stocks.in');
        Route::get('/out', StockOutIndex::class)->name('stocks.out');
        Route::get('/opname', StockOpnameIndex::class)->name('stocks.opname');
        Route::get('/balances', StockBalanceIndex::class)->name('stocks.balances');
        Route::get('/reservations', \App\Livewire\Admin\Stocks\ReservationsIndex::class)->name('stocks.reservations');
    });

    Route::get('expenses/materials', MaterialExpenseIndex::class)->name('expenses.materials.index');
    Route::get('expenses/general', GeneralExpenseIndex::class)->name('expenses.general.index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('bank-accounts', BankAccountsIndex::class)->name('bank-accounts.index');
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', CustomersIndex::class)->name('index');
        Route::get('/create', CustomersCreate::class)->name('create');
        Route::get('/{customer}/edit', CustomersEdit::class)->name('edit');
        Route::get('/trashed', \App\Livewire\Admin\Customers\Trashed::class)->name('trashed');
    });
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('/', EmployeesIndex::class)->name('index');
        Route::get('/create', EmployeesCreate::class)->name('create');
        Route::get('/{employee}/edit', EmployeesEdit::class)->name('edit');
    });


    // --- RUTE BARU UNTUK MANAJEMEN AKSES ---
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', UsersIndex::class)->name('index');
        Route::get('/trashed', \App\Livewire\Admin\Users\Trashed::class)->name('trashed');
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
        Route::get('/trashed', \App\Livewire\Admin\Product\Trashed::class)->name('trashed');
    });

    Route::get('units', UnitIndex::class)->name('units.index');

    Route::get('categories', CategoriesIndex::class)->name('categories.index');
    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', SuppliersIndex::class)->name('index');
        Route::get('/create', SuppliersCreate::class)->name('create');
        Route::get('/{supplier}/edit', SuppliersEdit::class)->name('edit');
        Route::get('/trashed', \App\Livewire\Admin\Suppliers\Trashed::class)->name('trashed');
    });
    Route::get('materials', MaterialsIndex::class)->name('materials.index');
    Route::get('materials/trashed', \App\Livewire\Admin\Materials\Trashed::class)->name('materials.trashed');
    Route::get('finishes', \App\Livewire\Admin\Finishes\Index::class)->name('finishes.index');
    Route::get('attendances', AttendancesIndex::class)->name('attendances.index');
    Route::get('employee-loans', EmployeeLoansIndex::class)->name('employee-loans.index');
    Route::prefix('warehouses')->name('warehouses.')->group(function () {
        Route::get('/', WarehousesIndex::class)->name('index');
        Route::get('/create', WarehousesCreate::class)->name('create');
        Route::get('/{warehouse}/edit', WarehousesEdit::class)->name('edit');
    });
    Route::get('branches', BranchesIndex::class)->name('branches.index');
    // -----------------------------------------

    // --- RUTE UNTUK PROFILE ---
    Route::get('/profile', ProfileEdit::class)->name('profile.edit');

    Route::get('reports/sales', SalesReport::class)->name('reports.sales');
    Route::get('reports/expenses', ExpenseReport::class)->name('reports.expenses');
    Route::get('reports/production', ProductionReport::class)->name('reports.production');
    Route::get('reports/financial', FinancialReport::class)->name('reports.financial');
    Route::get('reports/branches', BranchReport::class)->name('reports.branches');
    Route::get('accounting/overview', AccountingOverviewIndex::class)->name('accounting.overview');
    Route::get('accounting/cashflows', AccountingCashflowsIndex::class)->name('cashflows.index');
    Route::get('accounting/accounts', AccountingAccountsIndex::class)->name('accounts.index');
    Route::get('accounting/journals', AccountingJournalsIndex::class)->name('journals.index');

    Route::get('audit-logs', AuditLogsIndex::class)->name('audit-logs.index');
    Route::get('file-manager', FileManagerIndex::class)->name('file-manager.index');
    Route::get('file-manager/download', [FileManagerController::class, 'download'])->name('file-manager.download');
    Route::get('file-manager/thumbnail', [FileManagerController::class, 'thumbnail'])->name('file-manager.thumbnail');

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
