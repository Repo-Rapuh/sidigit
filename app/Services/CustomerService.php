<?php

namespace App\Services;

use App\Models\Customer;
use App\Repositories\CustomerRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerService
{
    protected $repository;
    public function __construct(CustomerRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getPaginated(): LengthAwarePaginator
    {
        return $this->repository->paginate();
    }

    public function store(array $data): Customer
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Customer
    {
        $customer = $this->repository->findOrFail($id);

        return $this->repository->update($customer, $data);
    }

    public function destroy(int $id): void
    {
        $customer = $this->repository->findOrFail($id);

        $this->repository->delete($customer);
    }

    public function find(int $id): Customer
    {
        return $this->repository->findOrFail($id);
    }

    public function memberTypes(): array
    {
        return Customer::MEMBER_TYPES;
    }
}
