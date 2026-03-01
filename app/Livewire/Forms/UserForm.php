<?php

namespace App\Livewire\Forms;

use App\Models\User;
use App\Models\Role;
use App\Services\UserService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
    public ?int $branch_id = null;
    public array $branch_ids = [];
    public ?int $employee_id = null;
    public bool $without_employee = true;

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

        $uniqueEmployee = Rule::unique(User::class, 'employee_id');
        if (! empty($this->id)) {
            $uniqueEmployee = $uniqueEmployee->ignore($this->id);
        }

        $employeeRules = $this->without_employee
            ? ['nullable', 'integer', 'exists:mst_employees,id', $uniqueEmployee]
            : ['required', 'integer', 'exists:mst_employees,id', $uniqueEmployee];

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', $uniqueUsername],
            'email' => ['required', 'string', 'email', 'max:255', $uniqueEmail],
            'password' => $passwordRules,
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'branch_ids' => ['required', 'array', 'min:1'],
            'branch_ids.*' => ['exists:branches,id'],
            'employee_id' => $employeeRules,
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'username.required' => 'Username wajib diisi.',
            'username.string' => 'Username harus berupa teks.',
            'username.max' => 'Username maksimal 255 karakter.',
            'username.unique' => 'Username sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'password.min' => 'Password minimal 8 karakter.',
            'role_id.required' => 'Role wajib dipilih.',
            'role_id.exists' => 'Role yang dipilih tidak valid.',
            'branch_id.required' => 'Cabang default wajib dipilih.',
            'branch_id.exists' => 'Cabang default tidak valid.',
            'branch_ids.required' => 'Pilih minimal 1 cabang.',
            'branch_ids.array' => 'Cabang harus berupa daftar.',
            'branch_ids.min' => 'Pilih minimal 1 cabang.',
            'branch_ids.*.exists' => 'Cabang yang dipilih tidak valid.',
            'employee_id.required' => 'Pegawai wajib dipilih.',
            'employee_id.integer' => 'Pegawai tidak valid.',
            'employee_id.exists' => 'Pegawai yang dipilih tidak valid.',
            'employee_id.unique' => 'Pegawai sudah memiliki akun.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'Nama',
            'username' => 'Username',
            'email' => 'Email',
            'password' => 'Password',
            'role_id' => 'Role',
            'branch_id' => 'Cabang Default',
            'branch_ids' => 'Akses Cabang',
            'employee_id' => 'Pegawai',
        ];
    }

    public function fillFromModel(User $user): void
    {
        $this->id = $user->id;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->role_id = $user->roles->first()?->id;
        $this->branch_id = $user->branch_id;
        $this->branch_ids = $user->branches->pluck('id')->map(fn ($id) => (int) $id)->toArray();
        if ($this->branch_id && !in_array($this->branch_id, $this->branch_ids, true)) {
            $this->branch_ids[] = $this->branch_id;
        }
        $this->employee_id = $user->employee_id;
        $this->without_employee = $user->employee_id === null;
        $this->password = '';
        $this->password_confirmation = '';
    }

    public function store(UserService $service): User
    {
        $this->validate();
        $this->ensureOwnerRoleCanBeAssigned();
        if (!in_array($this->branch_id, $this->branch_ids, true)) {
            $this->branch_ids[] = $this->branch_id;
        }

        $user = $service->store([
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
            'branch_id' => $this->branch_id,
            'employee_id' => $this->without_employee ? null : $this->employee_id,
        ]);

        $user->roles()->sync([$this->role_id]);
        $service->syncBranches($user->id, $this->branch_ids);

        return $user;
    }

    public function update(UserService $service): User
    {
        $this->validate();
        $this->ensureOwnerRoleCanBeAssigned();
        if (!in_array($this->branch_id, $this->branch_ids, true)) {
            $this->branch_ids[] = $this->branch_id;
        }

        $data = [
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'branch_id' => $this->branch_id,
            'employee_id' => $this->without_employee ? null : $this->employee_id,
        ];

        if (! empty($this->password)) {
            $data['password'] = $this->password;
        }

        $user = $service->update($this->id, $data);
        $user->roles()->sync([$this->role_id]);
        $service->syncBranches($user->id, $this->branch_ids);

        return $user;
    }

    protected function ensureOwnerRoleCanBeAssigned(): void
    {
        if (!$this->role_id) {
            return;
        }

        $role = Role::query()->select('slug')->find($this->role_id);
        if (!$role || $role->slug !== 'owner') {
            return;
        }

        $actor = auth()->user();
        if (!$actor) {
            return;
        }

        if ($actor->hasRoleSlug('superadmin')) {
            return;
        }

        throw ValidationException::withMessages([
            'role_id' => 'Role Owner hanya dapat ditetapkan oleh Superadmin.',
        ]);
    }
}
