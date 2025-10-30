<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerRequest;
use App\Services\CustomerService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class CustomerController extends Controller
{
    use AuthorizesRequests;
    protected $service;
    public function __construct( CustomerService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('customer.view');
        $customers = $this->service->getPaginated();

        return view('admin.customer.index', compact('customers'));
    }

    public function create()
    {
        $this->authorize('customer.create');
        $memberTypes = $this->service->memberTypes();

        return view('admin.customer.create', compact('memberTypes'));
    }

    public function store(CustomerRequest $request)
    {
        $this->authorize('customer.create');
        $customer = $this->service->store($request->validated());

        return redirect()
            ->route('customers.index')
            ->with('success', "Pelanggan {$customer->name} berhasil ditambahkan.");
    }

    public function edit(int $customer)
    {
        $this->authorize('customer.edit');
        $customer = $this->service->find($customer);
        $memberTypes = $this->service->memberTypes();

        return view('admin.customer.edit', compact('customer', 'memberTypes'));
    }

    public function update(CustomerRequest $request, int $customer)
    {
        $this->authorize('customer.edit');
        $customerModel = $this->service->update($customer, $request->validated());

        return redirect()
            ->route('customers.index')
            ->with('success', "Pelanggan {$customerModel->name} berhasil diperbarui.");
    }

    public function destroy(int $customer)
    {
        $this->authorize('customer.delete');
        $customerModel = $this->service->find($customer);
        $this->service->destroy($customer);

        return redirect()
            ->route('customers.index')
            ->with('success', "Pelanggan {$customerModel->name} berhasil dihapus.");
    }
}
