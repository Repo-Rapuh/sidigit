<?php

namespace App\Livewire\Admin\Users;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Edit User')]
class UsersEdit extends Component
{
    use AuthorizesRequests;

    public User $user;

    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public array $roles = [];

    public function mount(User $user): void
    {
        $this->authorize('users.edit');

        $this->user = $user->load('roles');

        $this->name = $this->user->name;
        $this->username = $this->user->username;
        $this->email = $this->user->email;
        $this->roles = $this->user->roles->pluck('id')->map(fn ($id) => (int) $id)->toArray();
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique(User::class, 'username')->ignore($this->user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($this->user->id)],
            'password' => ['nullable', 'string', 'confirmed', Password::min(8)],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,id'],
        ];
    }

    public function update(): void
    {
        $data = $this->validate();

        $this->user->fill([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
        ]);

        if (! empty($data['password'])) {
            $this->user->password = $data['password'];
        }

        $this->user->save();
        $this->user->roles()->sync($this->roles);

        session()->flash('success', 'User berhasil diperbarui.');

        $this->redirectRoute('users.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.users.edit', [
            'availableRoles' => Role::orderBy('name')->get(),
        ])->layoutData([
            'title' => 'Edit User',
        ]);
    }
}
