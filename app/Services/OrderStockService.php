<?php

namespace App\Services;

use App\Models\Material;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockMovement;

class OrderStockService
{
    protected OrderMaterialUsageService $materialUsageService;
    protected StockMovementService $stockMovementService;

    public function __construct(
        OrderMaterialUsageService $materialUsageService,
        StockMovementService $stockMovementService
    ) {
        $this->materialUsageService = $materialUsageService;
        $this->stockMovementService = $stockMovementService;
    }

    public function syncByStatus(Order $order): void
    {
        StockMovement::query()
            ->where('ref_type', 'order')
            ->where('ref_id', $order->id)
            ->delete();

        $status = (string) $order->status;
        $reserveStatuses = ['approval'];
        $outStatuses = ['produksi', 'qc', 'siap', 'diambil', 'selesai'];

        $type = null;
        $note = null;

        if (in_array($status, $reserveStatuses, true)) {
            $type = 'reserve';
            $note = 'Reservasi bahan untuk order';
        } elseif (in_array($status, $outStatuses, true)) {
            $type = 'out';
            $note = 'Pemakaian bahan untuk order';
        } else {
            return;
        }

        $order->loadMissing('items.material');
        foreach ($order->items as $orderItem) {
            $material = $orderItem->material ?: ($orderItem->material_id ? Material::find($orderItem->material_id) : null);
            if (!$material) {
                continue;
            }

            $this->createStockMovement($orderItem, $material, $type, $note);
        }
    }

    protected function createStockMovement(OrderItem $orderItem, Material $material, string $type, string $note): void
    {
        $qty = (float) $orderItem->qty;
        $lengthCm = $orderItem->length_cm !== null ? (float) $orderItem->length_cm : null;
        $widthCm = $orderItem->width_cm !== null ? (float) $orderItem->width_cm : null;

        $usage = $this->materialUsageService->calculate(
            $qty,
            $lengthCm,
            $widthCm,
            $material->roll_width_cm !== null ? (float) $material->roll_width_cm : null,
            $material->roll_waste_percent !== null ? (float) $material->roll_waste_percent : 0
        );

        $this->stockMovementService->store([
            'material_id' => $material->id,
            'type' => $type,
            'qty' => $usage,
            'unit_id' => $material->unit_id,
            'ref_type' => 'order',
            'ref_id' => $orderItem->order_id,
            'notes' => $note,
            'branch_id' => $orderItem->order?->branch_id,
        ]);
    }
}

