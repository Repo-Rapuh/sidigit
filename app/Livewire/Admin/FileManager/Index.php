<?php

namespace App\Livewire\Admin\FileManager;

use App\Models\Branch;
use App\Services\FileManagerService;
use App\Traits\WithErrorToast;
use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('File Manager')]
class Index extends Component
{
    use AuthorizesRequests;
    use WithErrorToast;
    use WithPageMeta;
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'all')]
    public string $folder = 'all';

    #[Url(except: 20)]
    public int $perPage = 20;

    public ?int $branch_id = null;

    public array $branchOptions = [];

    protected FileManagerService $service;

    public function boot(FileManagerService $service): void
    {
        $this->service = $service;
    }

    public function mount(): void
    {
        $this->authorize('file-manager.view');

        $branches = $this->resolveAllowedBranches();
        $this->branchOptions = $branches
            ->map(fn (Branch $branch) => ['id' => (int) $branch->id, 'name' => (string) $branch->name])
            ->values()
            ->all();

        $activeBranchId = session('active_branch_id');
        $allowedIds = collect($this->branchOptions)->pluck('id')->map(fn ($id) => (int) $id);

        if (! empty($activeBranchId) && $allowedIds->contains((int) $activeBranchId)) {
            $this->branch_id = (int) $activeBranchId;
        } elseif ($allowedIds->isNotEmpty()) {
            $this->branch_id = (int) $allowedIds->first();
        }

        $this->setPageMeta(
            'File Manager',
            'Kelola file upload per cabang dari object storage.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Settings', 'current' => false],
                ['label' => 'File Manager', 'current' => true],
            ]
        );
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFolder(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedBranchId(): void
    {
        $this->resetPage();
    }

    public function delete(string $encodedPath): void
    {
        $this->authorize('file-manager.delete');

        if (empty($this->branch_id)) {
            $this->dispatch('toast', message: 'Cabang belum dipilih.', type: 'warning');
            return;
        }

        $path = base64_decode($encodedPath, true);
        if ($path === false || $path === '') {
            $this->dispatch('toast', message: 'Path file tidak valid.', type: 'warning');
            return;
        }

        try {
            $this->service->deleteByBranch((int) $this->branch_id, $path);
            $this->dispatch('toast', message: 'File berhasil dihapus.', type: 'success');
        } catch (\Throwable $th) {
            $this->toastError($th, 'Gagal menghapus file.');
        }
    }

    public function render()
    {
        $folderOptions = collect();
        $files = $this->emptyPaginator();
        $storageDetails = [
            'total_files' => 0,
            'total_size' => 0,
            'branch_total_size' => 0,
            'client_total_size' => 0,
            'client_total_files' => 0,
            'quota_bytes' => 0,
            'remaining_bytes' => null,
            'used_percent' => null,
            'image_count' => 0,
            'document_count' => 0,
            'other_count' => 0,
            'folder_usage' => [],
            'latest_files' => [],
        ];

        if (! empty($this->branch_id)) {
            $folderOptions = $this->service->folderOptions((int) $this->branch_id);
            $files = $this->service->paginateByBranch(
                (int) $this->branch_id,
                $this->folder,
                $this->search,
                'desc',
                $this->perPage,
                $this->getPage()
            );
            $storageDetails = $this->service->storageDetails((int) $this->branch_id);
        }

        return view('livewire.admin.file-manager.index', [
            'files' => $files,
            'folderOptions' => $folderOptions,
            'storageDetails' => $storageDetails,
        ]);
    }

    protected function resolveAllowedBranches(): Collection
    {
        $user = auth()->user();
        if (! $user) {
            return collect();
        }

        if ($user->hasRoleSlug('superadmin')) {
            return Branch::query()
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        $branchIds = collect([$user->branch_id])
            ->merge($user->branches()->pluck('branches.id'))
            ->filter()
            ->unique()
            ->values();

        if ($branchIds->isEmpty()) {
            return collect();
        }

        return Branch::query()
            ->whereIn('id', $branchIds->all())
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    protected function emptyPaginator(): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            [],
            0,
            $this->perPage,
            1,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }
}
