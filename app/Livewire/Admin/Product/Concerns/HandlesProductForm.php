<?php

namespace App\Livewire\Admin\Product\Concerns;

use App\Models\Category;
use App\Models\Material;
use App\Models\Unit;

trait HandlesProductForm
{
    public array $categories = [];
    public array $units = [];
    public array $materialsByCategory = [];
    public array $materialsForSelectedCategory = [];
    public array $dimensionUnitIds = [];
    public bool $showDimensionFields = false;

    protected function loadReferenceData(): void
    {
        $this->categories = Category::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
            ])
            ->toArray();

        $this->units = Unit::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($unit) => [
                'id' => $unit->id,
                'name' => $unit->name,
            ])
            ->toArray();

        $this->materialsByCategory = $this->getMaterialsByCategory();

        $this->dimensionUnitIds = collect($this->units)
            ->filter(fn ($unit) => in_array(strtolower($unit['name']), ['cm'], true))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();
    }

    protected function refreshMaterialsForCategory(?int $categoryId = null): void
    {
        $categoryKey = $categoryId ?? $this->category_id;
        $categoryKey = $categoryKey ? (string) $categoryKey : null;

        $this->materialsForSelectedCategory = $categoryKey && isset($this->materialsByCategory[$categoryKey])
            ? $this->materialsByCategory[$categoryKey]
            : [];
    }

    protected function syncDimensionFields(bool $resetValues = false): void
    {
        $unitId = $this->unit_id ? (int) $this->unit_id : null;
        $shouldShow = $unitId && in_array($unitId, $this->dimensionUnitIds, true);

        $this->showDimensionFields = (bool) $shouldShow;

        if (!$shouldShow && $resetValues) {
            $this->length_cm = null;
            $this->width_cm = null;
        }
    }

    protected function getMaterialsByCategory(): array
    {
        return Material::query()
            ->with('unit')
            ->orderBy('name')
            ->get()
            ->groupBy('category_id')
            ->mapWithKeys(function ($materials, $categoryId) {
                return [
                    (string) $categoryId => $materials->map(function (Material $material) {
                        return [
                            'id' => (string) $material->id,
                            'name' => $material->name,
                            'unit' => optional($material->unit)->name,
                        ];
                    })->values()->toArray(),
                ];
            })
            ->toArray();
    }
}
