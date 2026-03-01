<?php

namespace App\Livewire\Admin\Menus;

use App\Services\MenuService;
use App\Traits\WithErrorToast;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Tambah Menu')]
class MenusCreate extends Component
{
    use AuthorizesRequests;
    use WithErrorToast;

    protected MenuService $service;

    public string $name = '';
    public ?int $parent_id = null;
    public ?string $route_name = null;
    public ?string $icon = null;
    public ?int $order = null;

    public function boot(MenuService $service): void
    {
        $this->service = $service;
    }

    public function mount(): void
    {
        $this->authorize('menu.create');
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:menus,id'],
            'route_name' => ['nullable', 'string', 'max:255', Rule::unique('menus', 'route_name')],
            'icon' => ['nullable', 'string', 'max:255'],
            'order' => ['required', 'integer'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama menu wajib diisi.',
            'name.string' => 'Nama menu harus berupa teks.',
            'name.max' => 'Nama menu maksimal 255 karakter.',
            'parent_id.integer' => 'Menu induk tidak valid.',
            'parent_id.exists' => 'Menu induk yang dipilih tidak valid.',
            'route_name.max' => 'Route name maksimal 255 karakter.',
            'route_name.unique' => 'Route name sudah digunakan.',
            'icon.max' => 'Ikon maksimal 255 karakter.',
            'order.required' => 'Urutan wajib diisi.',
            'order.integer' => 'Urutan harus berupa angka.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'Nama menu',
            'parent_id' => 'Menu induk',
            'route_name' => 'Route name',
            'icon' => 'Ikon',
            'order' => 'Urutan',
        ];
    }

    protected function toastValidation(ValidationException $e, ?string $fallback = null): void
    {
        $errors = $e->validator->errors()->all();
        if (!empty($errors)) {
            $message = "Periksa input:\n• " . implode("\n• ", $errors);
        } else {
            $message = $fallback ?: 'Periksa kembali input. Ada data yang belum sesuai.';
        }

        $this->dispatch('toast', message: $message, type: 'warning');
    }

    public function save(): void
    {
        try {
            $data = $this->validate();

            $this->service->store($data);

            session()->flash('success', 'Menu baru berhasil ditambahkan.');

            $this->redirectRoute('menus.index');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $th) {
            report($th);
            $this->toastError($th, 'Terjadi kesalahan saat menambahkan menu.');
        }
    }

    public function render()
    {
        return view('livewire.admin.menus.create', [
            'parentMenuOptions' => $this->parentMenuOptions(),
            'iconPresets' => $this->iconPresets(),
        ])->layoutData([
            'title' => 'Tambah Menu',
        ]);
    }

    protected function parentMenuOptions(): array
    {
        return $this->service->parentOptions()
            ->map(fn($menu) => [
                'id' => $menu->id,
                'label' => $menu->name,
            ])
            ->toArray();
    }

    protected function iconPresets(): array
    {
        return collect(config('menu.icons', []))
            ->keys()
            ->reject(fn($key) => $key === 'default')
            ->reject(fn($key) => Str::startsWith((string) $key, 'bi bi-'))
            ->map(fn($key) => [
                'id' => (string) $key,
                'label' => (string) $key,
            ])
            ->values()
            ->toArray();
    }
}
