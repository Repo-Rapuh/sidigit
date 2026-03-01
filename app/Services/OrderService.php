<?php

namespace App\Services;

use App\Models\Finish;
use App\Models\Material;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemFinish;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StockMovement;
use App\Repositories\OrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    protected OrderRepository $repository;
    protected OrderMaterialUsageService $materialUsageService;
    protected ProductionJobService $productionJobService;
    protected OrderStockService $orderStockService;
    protected AccountingAutoPostingService $autoPostingService;

    public function __construct(
        OrderRepository $repository,
        OrderMaterialUsageService $materialUsageService,
        ProductionJobService $productionJobService,
        OrderStockService $orderStockService,
        AccountingAutoPostingService $autoPostingService
    )
    {
        $this->repository = $repository;
        $this->materialUsageService = $materialUsageService;
        $this->productionJobService = $productionJobService;
        $this->orderStockService = $orderStockService;
        $this->autoPostingService = $autoPostingService;
    }

    public function query(): Builder
    {
        return $this->repository->query()->with(['customer']);
    }

    public function queryTrashed(): Builder
    {
        return $this->repository->queryTrashed()->with(['customer']);
    }

    public function getPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage);
    }

    public function store(array $data): Order
    {
        $items = $data['items'] ?? [];
        $payments = $data['payments'] ?? [];
        unset($data['items'], $data['payments']);

        return DB::transaction(function () use ($data, $items, $payments) {
            $data['order_no'] = $data['order_no'] ?? $this->generateOrderNo();
            $order = $this->repository->create($data);

            $this->syncItems($order, $items);
            $this->syncPayments($order, $payments);
            $this->recalculateTotals($order);
            $this->syncAutoPostingPayments($order);
            $this->orderStockService->syncByStatus($order);
            $freshOrder = $order->refresh();
            $this->productionJobService->syncByOrderStatus($freshOrder);
            $this->autoPostingService->syncOrderAccrual($freshOrder);
            $order->statusLogs()->create([
                'status' => $order->status,
                'changed_by' => auth()->id(),
                'note' => 'Order dibuat.',
            ]);

            return $order->fresh(['customer', 'items']);
        });
    }

    public function update(int $id, array $data): Order
    {
        $items = $data['items'] ?? [];
        $payments = $data['payments'] ?? [];
        $revisionReason = $data['revision_reason'] ?? null;
        unset($data['items'], $data['payments'], $data['revision_reason']);

        return DB::transaction(function () use ($id, $data, $items, $payments, $revisionReason) {
            $order = $this->repository->findOrFail($id);
            $oldStatus = $order->status;
            $canOverrideLockedOrder = auth()->user()?->can('workflow.override.locked-order') ?? false;

            if ($this->isLockedStatus($oldStatus) && !$canOverrideLockedOrder) {
                $nextStatus = (string) ($data['status'] ?? $oldStatus);
                $this->ensureRevisionReasonIfRequired($oldStatus, $nextStatus, $revisionReason);

                $this->repository->update($order, [
                    'status' => $nextStatus,
                ]);

                if ($oldStatus !== $order->status) {
                    $order->statusLogs()->create([
                        'status' => $order->status,
                        'changed_by' => auth()->id(),
                        'note' => $this->buildStatusLogNote($oldStatus, $order->status, 'Status diperbarui.', $revisionReason),
                    ]);
                }

                $this->orderStockService->syncByStatus($order);
                $freshOrder = $order->refresh();
                $this->productionJobService->syncByOrderStatus($freshOrder);
                $this->autoPostingService->syncOrderAccrual($freshOrder);

                return $order->fresh(['customer', 'items']);
            }

            $this->repository->update($order, $data);

            $this->clearItems($order);
            $this->syncItems($order, $items);

            $this->syncPayments($order, $payments);

            $this->recalculateTotals($order);
            $this->syncAutoPostingPayments($order);
            $this->orderStockService->syncByStatus($order);
            $freshOrder = $order->refresh();
            $this->productionJobService->syncByOrderStatus($freshOrder);
            $this->autoPostingService->syncOrderAccrual($freshOrder);

            if ($oldStatus !== $order->status) {
                $order->statusLogs()->create([
                    'status' => $order->status,
                    'changed_by' => auth()->id(),
                    'note' => 'Status diperbarui.',
                ]);
            }

            return $order->fresh(['customer', 'items']);
        });
    }

    public function addPayment(int $orderId, array $data): Payment
    {
        return DB::transaction(function () use ($orderId, $data) {
            $order = $this->repository->findOrFail($orderId);
            $remainingBeforePayment = max(0, (float) $order->grand_total - (float) $order->paid_amount);

            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $data['amount'],
                'method' => $data['method'] ?? 'cash',
                'paid_at' => $data['paid_at'] ?? now(),
                'notes' => $data['notes'] ?? null,
                'branch_id' => $order->branch_id,
            ]);

            $this->recalculateTotals($order);
            $this->autoPostingService->postPayment($order, $payment, $remainingBeforePayment);

            return $payment;
        });
    }

    public function destroy(int $id): void
    {
        DB::transaction(function () use ($id) {
            $order = $this->repository->findOrFail($id);
            $this->ensureCanBeDeleted($order);
            $paymentIds = $order->payments()->pluck('id')->all();

            $order->items()->delete();
            $order->payments()->delete();
            $this->autoPostingService->deleteBySource('order-accrual', (int) $order->id);
            $this->autoPostingService->deleteBySources('payment', $paymentIds);
            $this->repository->delete($order);
        });
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        DB::transaction(function () use ($ids) {
            $orders = $this->repository->query()->whereIn('id', $ids)->get();
            $paymentIds = [];

            foreach ($orders as $order) {
                $this->ensureCanBeDeleted($order);
                $paymentIds = array_merge($paymentIds, $order->payments()->pluck('id')->all());
            }

            foreach ($orders as $order) {
                $order->items()->delete();
                $order->payments()->delete();
                $this->autoPostingService->deleteBySource('order-accrual', (int) $order->id);
                $this->repository->delete($order);
            }

            $this->autoPostingService->deleteBySources('payment', $paymentIds);
        });
    }

    public function restore(int $id): void
    {
        $order = $this->repository->findTrashed($id);
        $order->restore();
        $order->items()->withTrashed()->restore();
        $order->payments()->withTrashed()->restore();
    }

    public function restoreMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        $orders = $this->repository->queryTrashed()->whereIn('id', $ids)->get();
        foreach ($orders as $order) {
            $order->restore();
            $order->items()->withTrashed()->restore();
            $order->payments()->withTrashed()->restore();
        }
    }

    public function find(int $id): Order
    {
        return $this->repository->query()
            ->with([
                'customer',
                'items',
                'items.product',
                'items.material',
                'items.finishes.finish',
                'payments',
                'statusLogs' => fn ($query) => $query->latest('created_at'),
                'statusLogs.changedByUser:id,name',
            ])
            ->findOrFail($id);
    }

    public function updateStatus(int $id, string $status, ?string $note = null, ?string $revisionReason = null): Order
    {
        return DB::transaction(function () use ($id, $status, $note, $revisionReason) {
            $order = $this->repository->findOrFail($id);
            $oldStatus = $order->status;

            $this->ensureRevisionReasonIfRequired($oldStatus, $status, $revisionReason);
            $this->repository->update($order, ['status' => $status]);

            if ($oldStatus !== $order->status) {
                $order->statusLogs()->create([
                    'status' => $order->status,
                    'changed_by' => auth()->id(),
                    'note' => $this->buildStatusLogNote($oldStatus, $order->status, $note, $revisionReason),
                ]);
            }

            $this->orderStockService->syncByStatus($order);
            $freshOrder = $order->refresh();
            $this->productionJobService->syncByOrderStatus($freshOrder);
            $this->autoPostingService->syncOrderAccrual($freshOrder);

            return $order->fresh(['customer', 'items']);
        });
    }

    public function requiresRevisionReason(string $fromStatus, string $toStatus): bool
    {
        if (!$this->isLockedStatus($fromStatus)) {
            return false;
        }

        $fromOrder = $this->statusOrder($fromStatus);
        $toOrder = $this->statusOrder($toStatus);

        if ($fromOrder === null || $toOrder === null) {
            return false;
        }

        return $toOrder < $fromOrder;
    }

    protected function syncItems(Order $order, array $items): void
    {
        foreach ($items as $index => $item) {
            $product = Product::query()
                ->with(['productMaterials' => fn ($query) => $query->whereNull('deleted_at')->select('id', 'product_id', 'material_id')])
                ->find($item['product_id'] ?? null);
            if (!$product) {
                continue;
            }

            $allowedMaterialIds = $product->productMaterials
                ->pluck('material_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
            $selectedMaterialId = isset($item['material_id']) && $item['material_id'] !== ''
                ? (int) $item['material_id']
                : null;
            $productType = (string) ($product->product_type ?? 'goods');

            if ($productType === 'goods' && !empty($allowedMaterialIds) && !$selectedMaterialId) {
                throw ValidationException::withMessages([
                    "items.$index.material_id" => 'Bahan wajib dipilih untuk produk barang ini.',
                ]);
            }

            if ($selectedMaterialId && !empty($allowedMaterialIds) && !in_array($selectedMaterialId, $allowedMaterialIds, true)) {
                throw ValidationException::withMessages([
                    "items.$index.material_id" => 'Bahan tidak sesuai dengan mapping bahan produk yang dipilih.',
                ]);
            }

            $material = !empty($item['material_id']) ? Material::find($item['material_id']) : null;
            $finishIds = $item['finish_ids'] ?? [];
            $finishTotal = $this->finishTotal($finishIds);

            [$hpp, $price, $total] = $this->calculateItemTotals($product, $material, $item, $finishTotal);

            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'material_id' => $material?->id,
                'unit_id' => $item['unit_id'] ?? $product->unit_id,
                'qty' => $item['qty'] ?? 1,
                'length_cm' => $item['length_cm'] ?? null,
                'width_cm' => $item['width_cm'] ?? null,
                'hpp' => $hpp,
                'price' => $price,
                'discount' => $item['discount'] ?? 0,
                'total' => $total,
            ]);

            $this->syncFinishes($orderItem, $finishIds);
        }
    }

    protected function clearItems(Order $order): void
    {
        $order->items()->delete();
        StockMovement::query()
            ->where('ref_type', 'order')
            ->where('ref_id', $order->id)
            ->delete();
        $order->productionJobs()->delete();
    }

    protected function syncFinishes(OrderItem $orderItem, array $finishIds): void
    {
        if (empty($finishIds)) {
            return;
        }

        $finishes = Finish::query()->whereIn('id', $finishIds)->get();
        foreach ($finishes as $finish) {
            OrderItemFinish::create([
                'order_item_id' => $orderItem->id,
                'finish_id' => $finish->id,
                'price' => $finish->price,
            ]);
        }
    }

    protected function syncPayments(Order $order, array $payments): void
    {
        foreach ($payments as $payment) {
            if (!empty($payment['id'])) {
                continue;
            }

            if (empty($payment['amount'])) {
                continue;
            }

            Payment::create([
                'order_id' => $order->id,
                'amount' => $payment['amount'],
                'method' => $payment['method'] ?? 'cash',
                'paid_at' => $payment['paid_at'] ?? now(),
                'notes' => $payment['notes'] ?? null,
                'branch_id' => $order->branch_id,
            ]);
        }
    }

    protected function clearPayments(Order $order): void
    {
        $order->payments()->delete();
    }

    protected function syncAutoPostingPayments(Order $order): void
    {
        $order->refresh();

        $grandTotal = (float) ($order->grand_total ?? 0);
        $runningPaid = 0.0;

        $payments = $order->payments()
            ->orderBy('paid_at')
            ->orderBy('id')
            ->get();

        foreach ($payments as $payment) {
            $remainingBeforePayment = max(0, $grandTotal - $runningPaid);
            $this->autoPostingService->postPayment($order, $payment, $remainingBeforePayment);
            $runningPaid += (float) ($payment->amount ?? 0);
        }
    }

    protected function calculateItemTotals(Product $product, ?Material $material, array $item, float $finishTotal): array
    {
        $qty = (float) ($item['qty'] ?? 1);
        $lengthCm = $item['length_cm'] !== null ? (float) $item['length_cm'] : null;
        $widthCm = $item['width_cm'] !== null ? (float) $item['width_cm'] : null;
        $discount = (float) ($item['discount'] ?? 0);

        $hppMaterial = $this->calculateMaterialCost($material, $qty, $lengthCm, $widthCm);
        $hpp = $hppMaterial + $finishTotal;

        if ($hpp <= 0 && $product->base_price > 0) {
            $hpp = (float) $product->base_price;
        }

        $price = $item['price'] !== null && $item['price'] !== ''
            ? (float) $item['price']
            : $this->defaultPrice($product, $hpp, $lengthCm, $widthCm);

        $total = max(0, ($price * $qty) - $discount);

        return [$hpp, $price, $total];
    }

    protected function calculateMaterialCost(?Material $material, float $qty, ?float $lengthCm, ?float $widthCm): float
    {
        if (!$material) {
            return 0;
        }

        $cost = (float) $material->cost_price;
        if ($cost <= 0) {
            return 0;
        }

        $usageQty = $this->materialUsageService->calculate(
            $qty,
            $lengthCm,
            $widthCm,
            $material->roll_width_cm !== null ? (float) $material->roll_width_cm : null,
            $material->roll_waste_percent !== null ? (float) $material->roll_waste_percent : 0
        );

        return $cost * $usageQty;
    }

    protected function finishTotal(array $finishIds): float
    {
        if (empty($finishIds)) {
            return 0;
        }

        return (float) Finish::query()->whereIn('id', $finishIds)->sum('price');
    }

    protected function defaultPrice(Product $product, float $hpp, ?float $lengthCm = null, ?float $widthCm = null): float
    {
        if ($product->sale_price > 0) {
            $price = (float) $product->sale_price;
            if ($lengthCm && $widthCm) {
                $areaM2 = ($lengthCm / 100) * ($widthCm / 100);
                $billableAreaM2 = max(1, $areaM2);
                return $price * $billableAreaM2;
            }

            return $price;
        }

        if ($hpp > 0) {
            return $hpp * 1.3;
        }

        return 0;
    }

    protected function recalculateTotals(Order $order): void
    {
        $order->refresh();

        $items = $order->items()->get();
        $totalHpp = (float) $items->sum('hpp');
        $totalPrice = (float) $items->sum(fn ($item) => (float) $item->price * (float) $item->qty);
        $totalDiscount = (float) $items->sum('discount');
        $grandTotal = (float) $items->sum('total');
        $paidAmount = (float) $order->payments()->sum('amount');

        $paymentStatus = $paidAmount <= 0 ? 'unpaid' : ($paidAmount < $grandTotal ? 'partial' : 'paid');

        $order->update([
            'total_hpp' => $totalHpp,
            'total_price' => $totalPrice,
            'total_discount' => $totalDiscount,
            'grand_total' => $grandTotal,
            'paid_amount' => $paidAmount,
            'payment_status' => $paymentStatus,
        ]);
    }

    protected function generateOrderNo(): string
    {
        $date = now()->format('Ymd');
        $count = Order::query()->whereDate('created_at', now()->toDateString())->count() + 1;

        return sprintf('ORD-%s-%04d', $date, $count);
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

    protected function statusOrder(string $status): ?int
    {
        return match ($status) {
            'draft' => 10,
            'quotation' => 20,
            'approval' => 30,
            'pembayaran' => 40,
            'desain' => 50,
            'produksi' => 60,
            'qc' => 70,
            'siap' => 80,
            'diambil' => 90,
            'selesai' => 100,
            default => null,
        };
    }

    protected function ensureRevisionReasonIfRequired(string $oldStatus, string $newStatus, ?string $revisionReason): void
    {
        if (!$this->requiresRevisionReason($oldStatus, $newStatus)) {
            return;
        }

        if (blank(trim((string) $revisionReason))) {
            throw ValidationException::withMessages([
                'revision_reason' => 'Alasan revisi wajib diisi saat menurunkan status dari fase Approval ke tahap sebelumnya.',
            ]);
        }
    }

    protected function ensureCanBeDeleted(Order $order): void
    {
        if (!in_array((string) $order->status, ['draft', 'quotation'], true)) {
            throw ValidationException::withMessages([
                'order_delete' => "Order {$order->order_no} tidak bisa dihapus. Hanya status Draft atau Quotation yang boleh dihapus.",
            ]);
        }

        $paidAmount = (float) $order->payments()->sum('amount');
        if ($paidAmount > 0) {
            throw ValidationException::withMessages([
                'order_delete' => "Order {$order->order_no} tidak bisa dihapus karena sudah memiliki pembayaran.",
            ]);
        }

        $hasStockMovements = StockMovement::query()
            ->where('ref_type', 'order')
            ->where('ref_id', $order->id)
            ->exists();

        if ($hasStockMovements) {
            throw ValidationException::withMessages([
                'order_delete' => "Order {$order->order_no} tidak bisa dihapus karena sudah memiliki pergerakan stok.",
            ]);
        }
    }

    protected function buildStatusLogNote(string $oldStatus, string $newStatus, ?string $defaultNote = null, ?string $revisionReason = null): string
    {
        $note = $defaultNote ?? 'Status diperbarui.';

        if (!$this->requiresRevisionReason($oldStatus, $newStatus)) {
            return $note;
        }

        $reason = trim((string) $revisionReason);
        if ($reason === '') {
            return $note;
        }

        return "{$note} Alasan revisi: {$reason}";
    }
}
