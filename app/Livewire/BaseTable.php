<?php

namespace App\Livewire;

use App\Traits\WithBulkSelection;
use App\Traits\WithModal;
use App\Traits\WithTablePagination;
use App\Traits\WithTableSearch;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

abstract class BaseTable extends Component
{
    use WithPagination;
    use WithTablePagination;
    use WithTableSearch;
    use WithModal;
    use WithBulkSelection;

    abstract protected function query();
    abstract protected function columns(): array;
    abstract protected function resetForm(): void;
    abstract protected function loadForm(int $id): void;
    abstract protected function formView(): ?string;

    protected function filtersView(): ?string
    {
        return null;
    }

    protected $paginationTheme = 'tailwind';

    #[Url(except: '', history: true)]
    public string $search = '';

    #[Url(except: 10)]
    public int $perPage = 10;

    #[Url(except: 'id')]
    public string $sortField = 'id';

    #[Url(except: 'asc')]
    public string $sortDirection = 'asc';

    protected function tableActions(): array
    {
        return [];
    }

    protected function rowActions(): array
    {
        return [];
    }

    protected function bulkActions(): array
    {
        return [];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return false;
    }

    public function render()
    {
        $rows = $this->applySorting($this->query())->paginate($this->perPage);

        return view('livewire.components.base-table', [
            'rows' => $rows,
            'columns' => $this->columns(),
            'tableActions' => $this->tableActions(),
            'rowActions' => $this->rowActions(),
            'bulkActions' => $this->bulkActions(),
            'formView' => $this->formView(),
            'filtersView' => $this->filtersView(),
        ]);
    }
}
