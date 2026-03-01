<?php

namespace App\Livewire\Admin\Orders;

use App\Livewire\BaseTable;
use App\Services\OrderService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class Table extends BaseTable
{
    protected OrderService $service;
    public string $statusCurrent = 'draft';
    public string $statusNext = 'draft';
    public ?string $statusRevisionReason = null;
    public bool $statusRequiresReason = false;
    public array $statusOptions = [];

    public function boot(OrderService $service): void
    {
        $this->service = $service;
        $this->statusOptions = $this->statusOptionsList();
    }

    protected function query()
    {
        return $this->applySearch(
            $this->service->query(),
            ['order_no']
        );
    }

    protected function resetForm(): void
    {
        $this->statusCurrent = 'draft';
        $this->statusNext = 'draft';
        $this->statusRevisionReason = null;
        $this->statusRequiresReason = false;
    }

    protected function loadForm(int $id): void
    {
        $order = $this->service->find($id);
        $this->statusCurrent = (string) $order->status;
        $this->statusNext = (string) $order->status;
        $this->statusRevisionReason = null;
        $this->statusRequiresReason = false;
    }

    public function delete(): void
    {
        try {
            $this->service->destroy($this->activeId);
            $this->closeModal();
            $this->dispatch('toast', message: 'Order berhasil dihapus.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            $this->closeModal();
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal menghapus order.');
        }
    }

    public function bulkDelete(): void
    {
        if (empty($this->selected)) {
            $this->dispatch('toast', message: 'Pilih minimal 1 data.', type: 'warning');
            return;
        }

        try {
            $this->service->destroyMany($this->selected);
            $this->selected = [];
            $this->selectAll = false;
            $this->closeModal();
            $this->dispatch('toast', message: 'Order terpilih berhasil dihapus.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            $this->closeModal();
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal menghapus order terpilih.');
        }
    }

    public function goCreate(): void
    {
        $this->redirectRoute('orders.create');
    }

    public function goEdit(int $id): void
    {
        $this->redirectRoute('orders.edit', ['order' => $id]);
    }

    public function goTrashed(): void
    {
        $this->redirectRoute('orders.trashed');
    }

    public function makeQuotation(int $id): void
    {
        try {
            $order = $this->service->find($id);
            if ($order->status !== 'draft') {
                $this->dispatch('toast', message: 'Quotation hanya bisa dibuat dari status Draft.', type: 'warning');
                return;
            }

            $this->service->updateStatus($id, 'quotation', 'Quotation dibuat.');
            $this->dispatch('toast', message: 'Quotation berhasil dibuat.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal membuat quotation.');
        }
    }

    public function approveQuotation(int $id): void
    {
        try {
            $order = $this->service->find($id);
            if ($order->status !== 'quotation') {
                $this->dispatch('toast', message: 'Approve hanya tersedia untuk quotation.', type: 'warning');
                return;
            }

            $this->service->updateStatus($id, 'approval', 'Quotation disetujui.');
            $this->dispatch('toast', message: 'Quotation berhasil disetujui.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal menyetujui quotation.');
        }
    }

    public function openStatusModal(int $id): void
    {
        $this->openEdit($id);
    }

    public function updatedStatusNext($value): void
    {
        $nextStatus = (string) $value;
        $this->statusRequiresReason = $this->service->requiresRevisionReason($this->statusCurrent, $nextStatus);

        if (!$this->statusRequiresReason) {
            $this->statusRevisionReason = null;
        }
    }

    public function update(): void
    {
        if (!$this->activeId) {
            return;
        }

        try {
            $rules = [
                'statusNext' => ['required', 'string', Rule::in($this->statusValues())],
                'statusRevisionReason' => ['nullable', 'string', 'min:5', 'max:500'],
            ];

            if ($this->service->requiresRevisionReason($this->statusCurrent, $this->statusNext)) {
                $rules['statusRevisionReason'] = ['required', 'string', 'min:5', 'max:500'];
            }

            $data = $this->validate($rules, [
                'statusNext.required' => 'Status baru wajib dipilih.',
                'statusNext.in' => 'Status baru tidak valid.',
                'statusRevisionReason.required' => 'Alasan revisi wajib diisi saat menurunkan status.',
                'statusRevisionReason.min' => 'Alasan revisi minimal :min karakter.',
                'statusRevisionReason.max' => 'Alasan revisi maksimal :max karakter.',
            ], [
                'statusNext' => 'status baru',
                'statusRevisionReason' => 'alasan revisi',
            ]);

            if ($this->statusCurrent === $data['statusNext']) {
                $this->dispatch('toast', message: 'Status tidak berubah.', type: 'warning');
                $this->closeModal();
                return;
            }

            $order = $this->service->updateStatus(
                $this->activeId,
                $data['statusNext'],
                'Status diperbarui dari daftar order.',
                $data['statusRevisionReason'] ?? null
            );

            $this->closeModal();
            $this->dispatch('toast', message: "Status order {$order->order_no} berhasil diperbarui.", type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal memperbarui status order.');
        }
    }

    protected function formView(): ?string
    {
        return 'livewire.admin.orders.forms.status-change';
    }

    protected function editModalTitle(): string
    {
        return 'Ubah Status Order';
    }

    protected function editModalActionLabel(): string
    {
        return 'Simpan Status';
    }

    protected function editModalWidth(): string
    {
        return 'lg';
    }

    protected function statusValues(): array
    {
        return collect($this->statusOptionsList())->pluck('value')->all();
    }

    protected function statusOptionsList(): array
    {
        return [
            ['value' => 'draft', 'label' => 'Draft'],
            ['value' => 'quotation', 'label' => 'Quotation'],
            ['value' => 'approval', 'label' => 'Approval Customer'],
            ['value' => 'pembayaran', 'label' => 'Pembayaran'],
            ['value' => 'desain', 'label' => 'Desain'],
            ['value' => 'produksi', 'label' => 'Produksi'],
            ['value' => 'qc', 'label' => 'QC'],
            ['value' => 'siap', 'label' => 'Siap Diambil/Dikirim'],
            ['value' => 'diambil', 'label' => 'Diambil'],
            ['value' => 'selesai', 'label' => 'Selesai'],
            ['value' => 'dibatalkan', 'label' => 'Dibatalkan'],
        ];
    }

    protected function rowActions(): array
    {
        return [
            [
                'label' => 'Buat Quotation',
                'method' => 'makeQuotation',
                'class' => 'text-amber-600',
                'icon' => 'file-text',
                'visible' => fn ($row) => $row->status === 'draft',
            ],
            [
                'label' => 'Approve Quotation',
                'method' => 'approveQuotation',
                'class' => 'text-emerald-600',
                'icon' => 'check',
                'visible' => fn ($row) => $row->status === 'quotation',
            ],
            [
                'label' => 'Input Pembayaran',
                'url' => fn ($row) => route('orders.payments.create', ['order' => $row->id]),
                'class' => 'text-emerald-600',
                'icon' => 'wallet',
            ],
            [
                'label' => 'Ubah Status',
                'method' => 'openStatusModal',
                'class' => 'text-indigo-600',
                'icon' => 'shuffle',
            ],
            [
                'label' => 'Edit Order',
                'method' => 'goEdit',
                'class' => 'text-brand-500',
                'icon' => 'pencil',
                'visible' => fn ($row) => in_array($row->status, ['draft', 'quotation'], true),
            ],
            [
                'label' => 'Lihat Order',
                'method' => 'goEdit',
                'class' => 'text-brand-500',
                'icon' => 'eye',
                'visible' => fn ($row) => !in_array($row->status, ['draft', 'quotation'], true),
            ],
            [
                'label' => 'Lihat Quotation',
                'url' => fn ($row) => route('orders.quotation', ['order' => $row->id]),
                'class' => 'text-gray-700 dark:text-gray-200',
                'icon' => 'file-text',
                'visible' => fn ($row) => $row->status !== 'draft',
            ],
            [
                'label' => 'Lihat Invoice',
                'url' => fn ($row) => route('orders.invoice', ['order' => $row->id]),
                'class' => 'text-gray-700 dark:text-gray-200',
                'icon' => 'printer',
                'visible' => fn ($row) => !in_array($row->status, ['draft', 'quotation'], true),
            ],
            [
                'label' => 'Print Struk',
                'url' => fn ($row) => route('orders.receipt', ['order' => $row->id, 'print' => 1]),
                'class' => 'text-gray-700 dark:text-gray-200',
                'icon' => 'printer',
                'visible' => fn ($row) => (float) ($row->paid_amount ?? 0) > 0,
            ],
            [
                'label' => 'Delete',
                'method' => 'confirmDelete',
                'class' => 'text-red-600',
                'icon' => 'trash-2',
                'visible' => fn ($row) => in_array((string) $row->status, ['draft', 'quotation'], true)
                    && (float) ($row->paid_amount ?? 0) <= 0,
            ],
        ];
    }

    protected function tableActions(): array
    {
        return [
            ['label' => 'Tambah Order', 'method' => 'goCreate', 'class' => 'bg-brand-500 hover:bg-brand-600 text-white', 'icon' => 'plus'],
            ['label' => 'Trashed', 'method' => 'goTrashed', 'class' => 'bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700', 'icon' => 'archive'],
        ];
    }

    protected function bulkActions(): array
    {
        return [
            'delete' => ['label' => 'Delete selected', 'method' => 'confirmBulkDelete'],
        ];
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Order No', 'field' => 'order_no', 'sortable' => true],
            ['label' => 'Customer', 'field' => 'customer.name', 'sortable' => false],
            ['label' => 'Status', 'view' => 'livewire.admin.orders.columns.status', 'sortable' => false],
            ['label' => 'Tracking', 'view' => 'livewire.admin.orders.columns.tracking', 'sortable' => false],
            [
                'label' => 'Total',
                'field' => 'grand_total',
                'sortable' => false,
                'format' => fn ($row) => number_format((float) $row->grand_total, 0, ',', '.'),
            ],
            ['label' => 'Pembayaran', 'field' => 'payment_status', 'sortable' => false],
            ['label' => 'Diubah pada', 'field' => 'updated_at', 'sortable' => false],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return true;
    }
}
