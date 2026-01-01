<?php

namespace App\Livewire\Admin\Categories;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Manajemen Kategori')]
class CategoriesIndex extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('category.view');
        $this->setPageMeta(
            'Daftar Kategori',
            'Kelola kategori untuk pengelompokan data.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Kategori', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.categories.index');
    }
}
