<?php

namespace App\Services;

use App\Models\Branch;
use App\Repositories\BranchRepository;
use App\Support\UploadPath;
use App\Support\UploadStorage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BranchService
{
    protected BranchRepository $repository;
    protected UploadQuotaService $uploadQuotaService;

    public function __construct(BranchRepository $repository, UploadQuotaService $uploadQuotaService)
    {
        $this->repository = $repository;
        $this->uploadQuotaService = $uploadQuotaService;
    }

    public function query(): Builder
    {
        return $this->repository->query();
    }

    public function getPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage);
    }

    public function store(array $data): Branch
    {
        return DB::transaction(function () use ($data) {
            $media = $this->extractMedia($data);
            $branch = $this->repository->create($data);
            $this->syncMedia($branch, $media);
            $this->syncMainBranch($branch, (bool) ($data['is_main'] ?? false));

            return $branch->fresh();
        });
    }

    public function update(int $id, array $data): Branch
    {
        return DB::transaction(function () use ($id, $data) {
            $branch = $this->repository->findOrFail($id);
            $media = $this->extractMedia($data);
            $branch = $this->repository->update($branch, $data);
            $this->syncMedia($branch, $media);
            $this->syncMainBranch($branch, (bool) ($data['is_main'] ?? $branch->is_main));

            return $branch->fresh();
        });
    }

    public function destroy(int $id): void
    {
        $branch = $this->repository->findOrFail($id);
        if ($branch->is_main) {
            throw new \RuntimeException('Cabang induk tidak bisa dihapus.');
        }

        $this->deleteMedia($branch);
        $this->repository->delete($branch);
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        $branches = $this->repository->query()->whereIn('id', $ids)->get();
        $protectedIds = $branches->where('is_main', true)->pluck('id')->all();

        $deleteIds = array_values(array_diff($ids, $protectedIds));
        if (empty($deleteIds)) {
            throw new \RuntimeException('Cabang induk tidak bisa dihapus.');
        }

        $branches = $this->repository->query()->whereIn('id', $deleteIds)->get();
        foreach ($branches as $branch) {
            $this->deleteMedia($branch);
        }
        $this->repository->query()->whereIn('id', $deleteIds)->delete();
    }

    public function find(int $id): Branch
    {
        return $this->repository->findOrFail($id);
    }

    protected function syncMainBranch(Branch $branch, bool $isMain): void
    {
        if (!$isMain) {
            if (!$this->repository->query()->where('is_main', true)->exists()) {
                $this->repository->update($branch, ['is_main' => true]);
            }
            return;
        }

        $this->repository->query()
            ->where('id', '!=', $branch->id)
            ->where('is_main', true)
            ->update(['is_main' => false]);
    }

    protected function extractMedia(array &$data): array
    {
        $media = [
            'logo' => $data['logo'] ?? null,
            'qris' => $data['qris'] ?? null,
        ];

        unset($data['logo'], $data['qris']);

        return $media;
    }

    protected function syncMedia(Branch $branch, array $media): void
    {
        $disk = UploadStorage::disk();
        $updates = [];

        if (($media['logo'] ?? null) instanceof UploadedFile) {
            $this->uploadQuotaService->assertCanUpload(
                $media['logo'],
                $branch->logo_path
            );
            $newPath = UploadPath::branchLogo((int) $branch->id, $media['logo']);
            $storedPath = $media['logo']->storeAs(dirname($newPath), basename($newPath), $disk);
            if ($storedPath === false) {
                throw new \RuntimeException('Gagal mengunggah logo cabang.');
            }

            $updates['logo_path'] = $storedPath;
            if (! empty($branch->logo_path)) {
                foreach (UploadStorage::deletionDisks() as $deleteDisk) {
                    Storage::disk($deleteDisk)->delete($branch->logo_path);
                }
            }
        }

        if (($media['qris'] ?? null) instanceof UploadedFile) {
            $this->uploadQuotaService->assertCanUpload(
                $media['qris'],
                $branch->qris_path
            );
            $newPath = UploadPath::branchQris((int) $branch->id, $media['qris']);
            $storedPath = $media['qris']->storeAs(dirname($newPath), basename($newPath), $disk);
            if ($storedPath === false) {
                throw new \RuntimeException('Gagal mengunggah QRIS cabang.');
            }

            $updates['qris_path'] = $storedPath;
            if (! empty($branch->qris_path)) {
                foreach (UploadStorage::deletionDisks() as $deleteDisk) {
                    Storage::disk($deleteDisk)->delete($branch->qris_path);
                }
            }
        }

        if (! empty($updates)) {
            $this->repository->update($branch, $updates);
        }
    }

    protected function deleteMedia(Branch $branch): void
    {
        if ($branch->logo_path) {
            foreach (UploadStorage::deletionDisks() as $deleteDisk) {
                Storage::disk($deleteDisk)->delete($branch->logo_path);
            }
        }
        if ($branch->qris_path) {
            foreach (UploadStorage::deletionDisks() as $deleteDisk) {
                Storage::disk($deleteDisk)->delete($branch->qris_path);
            }
        }
    }
}
