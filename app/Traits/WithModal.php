<?php

namespace App\Traits;

use Livewire\Attributes\On;

trait WithModal
{
    /** Modal state */
    public string $modalMaxWidth = 'md';
    public string $modalTitle = '';

    public string $modalActionLabel = 'Save';
    public string $modalActionMethod = 'create';
    public string $modalCancelLabel = 'Cancel';

    public bool $showFormModal = false;
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showDeleteModal = false;
    public bool $showBulkDeleteModal = false;

    /** Current row */
    public ?int $activeId = null;

    /** Generic modal handlers */
    public function openCreate()
    {
        $this->resetForm();

        $this->showEditModal = false;
        $this->showCreateModal = true;
        $this->showFormModal = true;

        $this->modalTitle = $this->createModalTitle();
        $this->modalActionLabel = $this->createModalActionLabel();
        $this->modalActionMethod = $this->createModalActionMethod();
        $this->modalCancelLabel = $this->modalCancelLabel();
        $this->modalMaxWidth = $this->createModalWidth();
    }

    public function openEdit(int $id)
    {
        $this->activeId = $id;
        $this->loadForm($id);

        $this->showCreateModal = false;
        $this->showEditModal = true;
        $this->showFormModal = true;

        $this->modalTitle = $this->editModalTitle();
        $this->modalActionLabel = $this->editModalActionLabel();
        $this->modalActionMethod = $this->editModalActionMethod();
        $this->modalCancelLabel = $this->modalCancelLabel();
        $this->modalMaxWidth = $this->editModalWidth();
    }

    public function confirmDelete(int $id)
    {
        $this->activeId = $id;
        $this->showDeleteModal = true;

        $this->modalMaxWidth = $this->deleteModalWidth();
    }

    public function confirmBulkDelete()
    {
        $this->showBulkDeleteModal = true;
        $this->modalMaxWidth = $this->deleteModalWidth();
    }

    #[On('closeModal')]
    public function closeModal()
    {
        $this->reset([
            'showFormModal',
            'showCreateModal',
            'showEditModal',
            'showDeleteModal',
            'showBulkDeleteModal',
            'activeId',
            'modalTitle',
            'modalActionLabel',
            'modalActionMethod',
            'modalCancelLabel',
        ]);

        $this->resetValidation();
        $this->resetForm();
    }

    /* ===================== WIDTH HOOKS ===================== */
    protected function createModalWidth(): string
    {
        return 'md';
    }

    protected function editModalWidth(): string
    {
        return 'md';
    }

    protected function deleteModalWidth(): string
    {
        return 'sm';
    }

    protected function createModalTitle(): string
    {
        return 'Tambah';
    }

    protected function editModalTitle(): string
    {
        return 'Update';
    }

    protected function createModalActionLabel(): string
    {
        return 'Simpan';
    }

    protected function editModalActionLabel(): string
    {
        return 'Update';
    }

    protected function createModalActionMethod(): string
    {
        return 'create';
    }

    protected function editModalActionMethod(): string
    {
        return 'update';
    }

    protected function modalCancelLabel(): string
    {
        return 'Kembali';
    }
}
