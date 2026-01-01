<?php

namespace App\Traits;

trait WithBulkSelection
{
    // Bulk selection
    public array $selected = [];
    public bool $selectAll = false;

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = $this->rowsOnPage()->pluck('id')->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function updatedSelected()
    {
        $this->selectAll = count($this->selected) === $this->rowsOnPage()->count();
    }

    protected function rowsOnPage()
    {
        return $this->applySorting($this->query())->paginate($this->perPage)->getCollection();
    }
}
