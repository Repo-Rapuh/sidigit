<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BankAccountRequest;
use App\Services\BankAccountService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BankAccountController extends Controller
{
    use AuthorizesRequests;
    protected $service;
    public function __construct(BankAccountService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('bank-account.view');
        $bankAccounts = $this->service->getPaginated();

        return view('admin.bank-account.index', compact('bankAccounts'));
    }

    public function create()
    {
        $this->authorize('bank-account.create');
        return view('admin.bank-account.create');
    }

    public function store(BankAccountRequest $request)
    {
        $this->authorize('bank-account.create');

        try {
            $bankAccount = $this->service->store($request->validated());

            return redirect()
                ->route('bank-accounts.index')
                ->with('success', "Rekening {$bankAccount->account_name} berhasil ditambahkan.");
        } catch (\Throwable $th) {
            report($th);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menambahkan rekening. Silakan coba lagi.');
        }
    }

    public function edit(int $bankAccount)
    {
        $this->authorize('bank-account.edit');
        try {
            $bankAccount = $this->service->find($bankAccount);

            return view('admin.bank-account.edit', compact('bankAccount'));
        } catch (\Throwable $th) {
            report($th);

            return redirect()
                ->route('bank-accounts.index')
                ->with('error', 'Data rekening tidak ditemukan atau tidak dapat diakses.');
        }
    }

    public function update(BankAccountRequest $request, int $bankAccount)
    {
        $this->authorize('bank-account.edit');
        try {
            $bankAccountModel = $this->service->update($bankAccount, $request->validated());

            return redirect()
                ->route('bank-accounts.index')
                ->with('success', "Rekening {$bankAccountModel->account_name} berhasil diperbarui.");
        } catch (\Throwable $th) {
            report($th);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data rekening. Silakan coba lagi.');
        }
    }

    public function destroy(int $bankAccount)
    {
        $this->authorize('bank-account.delete');
        try {
            $bankAccountModel = $this->service->find($bankAccount);
            $this->service->destroy($bankAccount);

            return redirect()
                ->route('bank-accounts.index')
                ->with('success', "Rekening {$bankAccountModel->account_name} berhasil dihapus.");
        } catch (\Throwable $th) {
            report($th);

            return redirect()
                ->route('bank-accounts.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data rekening. Silakan coba lagi.');
        }
    }
}
