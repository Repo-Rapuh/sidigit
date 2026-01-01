<?php

namespace App\Traits;

trait WithTablePagination
{
    public int $perPage = 10;
    public string $sortField = 'id';
    public string $sortDirection = 'asc';

    protected function applySorting($query)
    {
        return $query->orderBy($this->sortField, $this->sortDirection);
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();

        if ($this->selectAll) {
            $this->selected = $this->rowsOnPage()->pluck('id')->toArray();
        }
    }
}
