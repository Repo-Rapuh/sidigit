<?php

namespace App\Services;

use App\Enums\EmployeeStatus;
use App\Models\Employee;
use App\Repositories\EmployeeRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EmployeeService
{
    protected $repository;
    public function __construct(EmployeeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getPaginated(): LengthAwarePaginator
    {
        return $this->repository->paginate();
    }

    public function query(): Builder
    {
        return Employee::query();
    }

    public function store(array $data): Employee
    {
        $data = $this->preparePayload($data);

        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Employee
    {
        $employee = $this->repository->findOrFail($id);
        $data = $this->preparePayload($data, $employee);

        return $this->repository->update($employee, $data);
    }

    public function destroy(int $id): void
    {
        $employee = $this->repository->findOrFail($id);

        if ($employee->photo) {
            $disk = config('filesystems.default', 'public');
            Storage::disk($disk)->delete($employee->photo);
        }

        $this->repository->delete($employee);
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        $employees = Employee::query()->whereIn('id', $ids)->get();
        foreach ($employees as $employee) {
            if ($employee->photo) {
                $disk = config('filesystems.default', 'public');
                Storage::disk($disk)->delete($employee->photo);
            }
        }

        Employee::query()->whereIn('id', $ids)->delete();
    }

    public function find(int $id): Employee
    {
        return $this->repository->findOrFail($id);
    }

    public function statuses(): array
    {
        return array_map(
            static fn (EmployeeStatus $status) => $status->value,
            EmployeeStatus::cases()
        );
    }

    protected function preparePayload(array $data, ?Employee $employee = null): array
    {
        if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
            $disk = config('filesystems.default', 'public');
            $data['photo'] = $data['photo']->store('employee-photos', $disk);

            if ($employee && $employee->photo) {
                Storage::disk($disk)->delete($employee->photo);
            }
        } else {
            unset($data['photo']);
        }

        if (isset($data['status'])) {
            $data['status'] = EmployeeStatus::from($data['status']);
        }

        return $data;
    }
}
