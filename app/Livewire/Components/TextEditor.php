<?php

namespace App\Livewire\Components;

use Livewire\Attributes\Modelable;
use Livewire\Component;

class TextEditor extends Component
{
    #[Modelable]
    public $value = '';

    public ?string $label = null;
    public string $placeholder = 'Tulis...';
    public bool $required = false;
    public array $headings = ['paragraph', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

    public function render()
    {
        return view('livewire.components.text-editor');
    }
}
