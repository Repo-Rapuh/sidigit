<?php

namespace App\Traits;

trait WithTableSearch
{
    // Common state
    public string $search = '';

    // Filters array - child can populate shape
    public array $filters = [];

    protected function applySearch($query, array $columns)
    {
        if ($this->search === null || $this->search === '') {
            return $query;
        }

        return $query->where(function ($q) use ($columns) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', "%{$this->search}%");
            }
        });
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilters()
    {
        $this->resetPage();
    }
}
