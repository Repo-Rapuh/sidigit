<?php

namespace App\Services;

use App\Enums\EmployeeStatus;
use App\Models\Employee;
use App\Repositories\EmployeeRepository;
use App\Support\BranchContext;
use App\Support\UploadPath;
use App\Support\UploadStorage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmployeeService
{
    protected $repository;
    protected UploadQuotaService $uploadQuotaService;

    public function __construct(EmployeeRepository $repository, UploadQuotaService $uploadQuotaService)
    {
        $this->repository = $repository;
        $this->uploadQuotaService = $uploadQuotaService;
    }

    public function getPaginated(): LengthAwarePaginator
    {
        return $this->repository->paginate();
    }

    public function query(): Builder
    {
        return $this->repository->query();
    }

    public function store(array $data): Employee
    {
        return DB::transaction(function () use ($data) {
            $data = $this->preparePayload($data);

            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): Employee
    {
        return DB::transaction(function () use ($id, $data) {
            $employee = $this->repository->findOrFail($id);
            $data = $this->preparePayload($data, $employee);

            return $this->repository->update($employee, $data);
        });
    }

    public function destroy(int $id): void
    {
        $employee = $this->repository->findOrFail($id);

        if ($employee->photo) {
            foreach (UploadStorage::deletionDisks() as $deleteDisk) {
                Storage::disk($deleteDisk)->delete($employee->photo);
            }
        }

        $this->repository->delete($employee);
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        $employees = $this->repository->query()->whereIn('id', $ids)->get();
        foreach ($employees as $employee) {
            if ($employee->photo) {
                foreach (UploadStorage::deletionDisks() as $deleteDisk) {
                    Storage::disk($deleteDisk)->delete($employee->photo);
                }
            }
        }

        $this->repository->query()->whereIn('id', $ids)->delete();
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
            $disk = UploadStorage::disk();
            $branchId = $this->resolveBranchId($employee, $data);
            $this->uploadQuotaService->assertCanUpload(
                $data['photo'],
                $employee?->photo
            );
            $newPath = UploadPath::employeePhoto($branchId, $data['photo']);
            $storedPath = $data['photo']->storeAs(dirname($newPath), basename($newPath), $disk);

            if ($storedPath === false) {
                throw new \RuntimeException('Gagal mengunggah foto karyawan.');
            }

            $data['photo'] = $storedPath;

            if ($employee && $employee->photo) {
                foreach (UploadStorage::deletionDisks() as $deleteDisk) {
                    Storage::disk($deleteDisk)->delete($employee->photo);
                }
            }
        } else {
            unset($data['photo']);
        }

        if (isset($data['status'])) {
            $data['status'] = EmployeeStatus::from($data['status']);
        }

        return $data;
    }

    protected function resolveBranchId(?Employee $employee, array $data): int
    {
        if (! empty($employee?->branch_id)) {
            return (int) $employee->branch_id;
        }

        if (! empty($data['branch_id'])) {
            return (int) $data['branch_id'];
        }

        $activeBranchId = BranchContext::activeBranchId();
        if (! empty($activeBranchId)) {
            return (int) $activeBranchId;
        }

        return 1;
    }
}
