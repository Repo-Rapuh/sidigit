<?php

namespace App\Livewire\Forms;

use App\Enums\EmployeeStatus;
use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Validation\Rule;
use Livewire\Form;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class EmployeeForm extends Form
{
    use WithFileUploads;

    public ?int $id = null;

    public string $name = '';
    public ?string $address = null;
    public ?string $phone_number = null;
    public ?string $email = null;
    public ?array $photo = [];
    public ?string $salary = null;
    public string $status = '';

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:128'],
            'phone_number' => ['nullable', 'string', 'max:16'],
            'email' => [
                'nullable',
                'email',
                'max:64',
                Rule::unique('mst_employees', 'email')->ignore($this->id),
            ],
            'photo' => ['nullable', 'array'],
            'salary' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::enum(EmployeeStatus::class)],
        ];
    }

    public function fillFromModel(Employee $employee): void
    {
        $this->id = $employee->id;
        $this->name = $employee->name;
        $this->address = $employee->address;
        $this->phone_number = $employee->phone_number;
        $this->email = $employee->email;
        $this->photo = [];
        $this->salary = $employee->salary !== null ? (string) $employee->salary : null;
        $this->status = $employee->status?->value ?? '';
    }

    public function store(EmployeeService $service): void
    {
        $this->validate();

        $service->store($this->payload());
    }

    public function update(EmployeeService $service): void
    {
        $this->validate();

        $service->update($this->id, $this->payload());
    }

    protected function payload(): array
    {
        return [
            'name' => $this->name,
            'address' => $this->address,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'photo' => $this->resolvedPhoto(),
            'salary' => $this->salary,
            'status' => $this->status,
        ];
    }

    protected function resolvedPhoto(): ?TemporaryUploadedFile
    {
        if (empty($this->photo)) {
            return null;
        }

        $firstPhoto = $this->photo[0] ?? null;
        if (!is_array($firstPhoto)) {
            return null;
        }

        $tmpFilename = $firstPhoto['tmpFilename'] ?? $firstPhoto['path'] ?? null;
        if (empty($tmpFilename)) {
            return null;
        }

        return TemporaryUploadedFile::createFromLivewire($tmpFilename);
    }
}
