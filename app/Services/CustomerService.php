<?php

namespace App\Services;

use App\Enums\CustomerMemberType;
use App\Models\Customer;
use App\Repositories\CustomerRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

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

    public function query(): Builder
    {
        return Customer::query();
    }

    public function store(array $data): Customer
    {
        $data['member_type'] = CustomerMemberType::from($data['member_type']);

        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Customer
    {
        $customer = $this->repository->findOrFail($id);

        $data['member_type'] = CustomerMemberType::from($data['member_type']);

        return $this->repository->update($customer, $data);
    }

    public function destroy(int $id): void
    {
        $customer = $this->repository->findOrFail($id);

        $this->repository->delete($customer);
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        Customer::query()->whereIn('id', $ids)->delete();
    }

    public function find(int $id): Customer
    {
        return $this->repository->findOrFail($id);
    }

    public function memberTypes(): array
    {
        return array_map(
            static fn (CustomerMemberType $type) => $type->value,
            CustomerMemberType::cases()
        );
    }
}
