<?php

namespace App\Livewire\Forms;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Form;

class UserForm extends Form
{
    public ?int $id = null;
    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public ?int $role_id = null;

    public bool $requirePassword = true;

    protected function rules(): array
    {
        $uniqueUsername = Rule::unique(User::class, 'username');
        $uniqueEmail = Rule::unique(User::class, 'email');

        if (! empty($this->id)) {
            $uniqueUsername = $uniqueUsername->ignore($this->id);
            $uniqueEmail = $uniqueEmail->ignore($this->id);
        }

        $passwordRules = $this->requirePassword
            ? ['required', 'string', 'confirmed', Password::min(8)]
            : ['nullable', 'string', 'confirmed', Password::min(8)];

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', $uniqueUsername],
            'email' => ['required', 'string', 'email', 'max:255', $uniqueEmail],
            'password' => $passwordRules,
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ];
    }

    public function fillFromModel(User $user): void
    {
        $this->id = $user->id;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->role_id = $user->roles->first()?->id;
        $this->password = '';
        $this->password_confirmation = '';
    }

    public function store(UserService $service): User
    {
        $this->validate();

        $user = $service->store([
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
        ]);

        $user->roles()->sync([$this->role_id]);

        return $user;
    }

    public function update(UserService $service): User
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
        ];

        if (! empty($this->password)) {
            $data['password'] = $this->password;
        }

        $user = $service->update($this->id, $data);
        $user->roles()->sync([$this->role_id]);

        return $user;
    }
}
