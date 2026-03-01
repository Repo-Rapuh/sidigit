<?php

namespace App\Livewire\Admin\Orders;

use App\Livewire\Admin\Orders\Concerns\HandlesOrderForm;
use App\Services\OrderService;
use App\Traits\WithErrorToast;
use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Edit Order')]
class Edit extends Component
{
    use AuthorizesRequests;
    use WithErrorToast;
    use WithPageMeta;
    use HandlesOrderForm;

    public int $orderId;
    public ?int $customer_id = null;
    public string $status = 'draft';
    public string $order_date;
    public ?string $deadline = null;
    public ?string $notes = null;
    public bool $isApprovalLocked = false;

    public array $items = [];
    public array $payments = [];

    protected OrderService $service;

    public function boot(OrderService $service): void
    {
        $this->service = $service;
    }

    public function mount(int $order): void
    {
        $this->authorize('order.edit');
        $this->loadReferenceData();

        $orderModel = $this->service->find($order);

        $this->orderId = $orderModel->id;
        $this->customer_id = $orderModel->customer_id;
        $this->status = $orderModel->status;
        $this->order_date = $orderModel->order_date?->format('Y-m-d') ?? now()->format('Y-m-d');
        $this->deadline = $orderModel->deadline?->format('Y-m-d');
        $this->notes = $orderModel->notes;
        $this->isApprovalLocked = $this->isLockedStatus($orderModel->status) && !$this->canOverrideLockedOrder();

        $this->items = $orderModel->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'material_id' => $item->material_id,
                'material_ids' => $this->productMaterialMap[$item->product_id] ?? [],
                'unit_id' => $item->unit_id,
                'qty' => (float) $item->qty,
                'length_cm' => $item->length_cm,
                'width_cm' => $item->width_cm,
                'price' => (float) $item->price,
                'price_source' => 'manual',
                'discount' => (float) $item->discount,
                'finish_ids' => $item->finishes?->pluck('finish_id')->toArray() ?? [],
            ];
        })->toArray();

        $this->payments = $orderModel->payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'amount' => (float) $payment->amount,
                'method' => $payment->method,
                'paid_at' => $payment->paid_at?->format('Y-m-d H:i'),
                'notes' => $payment->notes,
            ];
        })->toArray();

        if (empty($this->items)) {
            $this->items = [$this->newItem()];
        }

        $this->setPageMeta(
            $this->isApprovalLocked ? 'Lihat Order' : 'Edit Order',
            $this->isApprovalLocked
                ? 'Order status approval ke atas atau dibatalkan bersifat read-only. Ubah status melalui aksi di daftar order.'
                : 'Perbarui data order dan item.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Order', 'url' => route('orders.index')],
                ['label' => $this->isApprovalLocked ? 'Lihat' : 'Edit', 'current' => true],
            ]
        );
    }

    protected function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'exists:mst_customers,id'],
            'status' => ['required', 'string'],
            'order_date' => ['required', 'date'],
            'deadline' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:mst_products,id'],
            'items.*.material_id' => ['nullable', 'exists:mst_materials,id'],
            'items.*.unit_id' => ['required', 'exists:mst_units,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.length_cm' => ['nullable', 'numeric', 'min:0'],
            'items.*.width_cm' => ['nullable', 'numeric', 'min:0'],
            'items.*.price' => ['nullable', 'integer', 'min:0'],
            'items.*.discount' => ['nullable', 'integer', 'min:0'],
            'items.*.finish_ids' => ['nullable', 'array'],
            'items.*.finish_ids.*' => ['integer', 'exists:finishes,id'],
            'payments' => ['nullable', 'array'],
            'payments.*.id' => ['nullable', 'integer', \Illuminate\Validation\Rule::exists('payments', 'id')->where('order_id', $this->orderId)],
            'payments.*.amount' => ['nullable', 'integer', 'min:0'],
            'payments.*.method' => ['nullable', 'string'],
            'payments.*.paid_at' => ['nullable', 'date'],
            'payments.*.notes' => ['nullable', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'exists' => ':attribute tidak ditemukan.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'numeric' => ':attribute harus berupa angka.',
            'integer' => ':attribute harus berupa angka bulat.',
            'string' => ':attribute harus berupa teks.',
            'array' => ':attribute tidak valid.',

            'items.min' => 'Minimal harus ada 1 item order.',
            'items.*.qty.min' => 'Qty minimal 1.',
            'items.*.length_cm.min' => 'Panjang minimal 0.',
            'items.*.width_cm.min' => 'Lebar minimal 0.',
            'items.*.price.min' => 'Harga jual minimal 0.',
            'items.*.discount.min' => 'Diskon minimal 0.',

        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'customer_id' => 'customer',
            'status' => 'status',
            'order_date' => 'tanggal order',
            'deadline' => 'deadline',
            'notes' => 'catatan',

            'items' => 'item order',
            'items.*.product_id' => 'produk',
            'items.*.material_id' => 'bahan',
            'items.*.unit_id' => 'satuan',
            'items.*.qty' => 'qty',
            'items.*.length_cm' => 'panjang',
            'items.*.width_cm' => 'lebar',
            'items.*.price' => 'harga jual',
            'items.*.discount' => 'diskon',
            'items.*.finish_ids' => 'finishing',
            'items.*.finish_ids.*' => 'finishing',

            'payments.*.amount' => 'jumlah pembayaran',
            'payments.*.method' => 'metode pembayaran',
            'payments.*.paid_at' => 'tanggal pembayaran',
            'payments.*.notes' => 'catatan pembayaran',
        ];
    }

    public function save(): void
    {
        try {
            if ($this->isApprovalLocked) {
                $this->dispatch(
                    'toast',
                    message: 'Order status Approval ke atas atau Dibatalkan hanya dapat dilihat. Ubah status lewat aksi pada daftar order.',
                    type: 'warning'
                );
                return;
            }

            $data = $this->validate();
            $this->validateItemMaterialRequirements($data['items'] ?? []);

            $order = $this->service->update($this->orderId, [
                'customer_id' => $data['customer_id'] ?? null,
                'status' => $data['status'],
                'order_date' => $data['order_date'],
                'deadline' => $data['deadline'] ?? null,
                'notes' => $data['notes'] ?? null,
                'items' => $data['items'],
                'payments' => $data['payments'] ?? [],
            ]);

            $this->syncStatusState($order->status);

            session()->flash('toast', [
                'message' => "Order {$order->order_no} berhasil diperbarui.",
                'type' => 'success',
            ]);
            $this->redirectRoute('orders.index');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $th) {
            report($th);
            $this->toastError($th, 'Terjadi kesalahan saat memperbarui order.');
        }
    }

    protected function isLockedStatus(string $status): bool
    {
        return in_array($status, [
            'approval',
            'pembayaran',
            'desain',
            'produksi',
            'qc',
            'siap',
            'diambil',
            'selesai',
            'dibatalkan',
        ], true);
    }

    protected function canOverrideLockedOrder(): bool
    {
        return auth()->user()?->can('workflow.override.locked-order') ?? false;
    }

    public function makeQuotation(): void
    {
        try {
            if ($this->status !== 'draft') {
                $this->dispatch('toast', message: 'Quotation hanya bisa dibuat dari status Draft.', type: 'warning');
                return;
            }

            $order = $this->service->updateStatus($this->orderId, 'quotation', 'Quotation dibuat.');
            $this->syncStatusState($order->status);
            $this->dispatch('toast', message: 'Quotation berhasil dibuat.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal membuat quotation.');
        }
    }

    public function approveQuotation(): void
    {
        try {
            if ($this->status !== 'quotation') {
                $this->dispatch('toast', message: 'Approve hanya tersedia untuk quotation.', type: 'warning');
                return;
            }

            $order = $this->service->updateStatus($this->orderId, 'approval', 'Quotation disetujui.');
            $this->syncStatusState($order->status);
            $this->dispatch('toast', message: 'Quotation berhasil disetujui.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal menyetujui quotation.');
        }
    }

    protected function syncStatusState(string $status): void
    {
        $this->status = $status;
        $this->isApprovalLocked = $this->isLockedStatus($status) && !$this->canOverrideLockedOrder();
    }

    public function render()
    {
        return view('livewire.admin.orders.edit');
    }
}
