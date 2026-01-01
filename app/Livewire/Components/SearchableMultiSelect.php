<?php

namespace App\Livewire\Components;

use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class SearchableMultiSelect extends Component
{
    #[Modelable]
    public $value = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    #[Locked]
    public array $options = [];

    public string $placeholder = 'Pilih opsi';
    public string $searchPlaceholder = 'Cari...';
    public string $emptyText = 'Tidak ada hasil';
    public string $optionLabelKey = 'label';
    public string $optionValueKey = 'id';
    public bool $allowClear = true;
    public bool $required = false;

    public function render()
    {
        return view('livewire.components.searchable-multi-select');
    }
}
