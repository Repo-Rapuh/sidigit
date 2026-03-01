<?php

namespace App\Livewire\Admin\Menus;

use App\Services\MenuService;
use App\Traits\WithErrorToast;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Edit Menu')]
class MenusEdit extends Component
{
    use AuthorizesRequests;
    use WithErrorToast;

    public int $menuId;
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

    public function mount(int $menu): void
    {
        $this->authorize('menu.edit');

        $this->menuId = $menu;
        $menuModel = $this->service->find($this->menuId);

        $this->name = $menuModel->name;
        $this->parent_id = $menuModel->parent_id;
        $this->route_name = $menuModel->route_name;
        $this->icon = $menuModel->icon;
        $this->order = $menuModel->order;
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:menus,id'],
            'route_name' => ['nullable', 'string', 'max:255', Rule::unique('menus', 'route_name')->ignore($this->menuId)],
            'icon' => ['nullable', 'string', 'max:255'],
            'order' => ['required', 'integer'],
        ];
    }

    public function update(): void
    {
        $data = $this->validate();

        try {
            $this->service->update($this->menuId, $data);

            session()->flash('success', 'Menu berhasil diperbarui.');

            $this->redirectRoute('menus.index');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $th) {
            report($th);
            $this->toastError($th, 'Terjadi kesalahan saat memperbarui menu.');
        }
    }

    public function render()
    {
        return view('livewire.admin.menus.edit', [
            'parentMenuOptions' => $this->parentMenuOptions(),
            'iconPresets' => $this->iconPresets(),
        ])->layoutData([
            'title' => 'Edit Menu',
        ]);
    }

    protected function parentMenuOptions(): array
    {
        return $this->service->parentOptions($this->menuId)
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
