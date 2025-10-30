<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerRepository
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Customer::query()
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findOrFail(int $id): Customer
    {
        return Customer::query()->findOrFail($id);
    }

    public function create(array $data): Customer
    {
        return Customer::query()->create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer;
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }
}
