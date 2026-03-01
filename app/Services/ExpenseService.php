<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Material;
use App\Models\StockMovement;
use App\Repositories\ExpenseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    protected ExpenseRepository $repository;
    protected AccountingAutoPostingService $autoPostingService;

    public function __construct(ExpenseRepository $repository, AccountingAutoPostingService $autoPostingService)
    {
        $this->repository = $repository;
        $this->autoPostingService = $autoPostingService;
    }

    public function query(): Builder
    {
        return $this->repository->query()->with(['material', 'supplier', 'unit']);
    }

    public function queryByType(string $type): Builder
    {
        return $this->query()->where('type', $type);
    }

    public function getPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage);
    }

    public function store(array $data): Expense
    {
        $payload = $this->normalize($data);

        return DB::transaction(function () use ($payload) {
            $expense = $this->repository->create($payload);
            $this->syncStock($expense, $payload);
            $this->autoPostingService->syncExpense($expense);

            return $expense;
        });
    }

    public function update(int $id, array $data): Expense
    {
        $payload = $this->normalize($data);

        return DB::transaction(function () use ($id, $payload) {
            $expense = $this->repository->findOrFail($id);
            $this->repository->update($expense, $payload);

            StockMovement::query()
                ->where('ref_type', 'expense')
                ->where('ref_id', $expense->id)
                ->delete();

            $this->syncStock($expense, $payload);
            $this->autoPostingService->syncExpense($expense);

            return $expense;
        });
    }

    public function destroy(int $id): void
    {
        DB::transaction(function () use ($id) {
            $expense = $this->repository->findOrFail($id);
            StockMovement::query()
                ->where('ref_type', 'expense')
                ->where('ref_id', $expense->id)
                ->delete();
            $this->autoPostingService->deleteBySource('expense', (int) $expense->id);
            $this->repository->delete($expense);
        });
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        DB::transaction(function () use ($ids) {
            StockMovement::query()
                ->where('ref_type', 'expense')
                ->whereIn('ref_id', $ids)
                ->delete();
            $this->autoPostingService->deleteBySources('expense', $ids);
            $this->repository->query()->whereIn('id', $ids)->delete();
        });
    }

    public function find(int $id): Expense
    {
        return $this->repository->query()->with(['material', 'supplier'])->findOrFail($id);
    }

    protected function syncStock(Expense $expense, array $payload): void
    {
        if (($payload['type'] ?? 'general') !== 'material') {
            return;
        }

        $qtyBase = (float) ($payload['qty_base'] ?? 0);
        if ($qtyBase <= 0) {
            return;
        }

        if (empty($payload['material_id'])) {
            return;
        }

        StockMovement::create([
            'material_id' => $payload['material_id'],
            'type' => 'in',
            'qty' => $qtyBase,
            'ref_type' => 'expense',
            'ref_id' => $expense->id,
            'notes' => $payload['notes'] ?? 'Pembelian bahan',
        ]);

        $this->updateMaterialCost($payload['material_id'], $qtyBase, (float) ($payload['unit_cost_base'] ?? 0));
    }

    protected function normalize(array $data): array
    {
        $type = $data['type'] ?? 'general';
        $qty = $data['qty'] ?? null;
        $unitCost = $data['unit_cost'] ?? null;
        $amount = $data['amount'] ?? null;

        if ($amount === null && $qty !== null && $unitCost !== null) {
            $amount = (float) $qty * (float) $unitCost;
        }

        if ($type !== 'material') {
            $data['material_id'] = null;
            $data['supplier_id'] = null;
            $data['unit_id'] = null;
            $data['qty'] = null;
            $data['unit_cost'] = null;
            $data['qty_base'] = null;
            $data['unit_cost_base'] = null;
        }

        $material = null;
        $qtyBase = null;
        $unitCostBase = null;
        if ($type === 'material' && !empty($data['material_id'])) {
            $material = Material::find($data['material_id']);
            if ($material && empty($data['unit_id'])) {
                $data['unit_id'] = $material->purchase_unit_id ?: $material->unit_id;
            }
            $conversion = $this->resolveConversion($material, $data['unit_id'] ?? null);
            $qtyBase = $qty !== null ? (float) $qty * $conversion : null;
            $unitCostBase = $qtyBase && $amount !== null ? (float) $amount / $qtyBase : null;
        }

        return [
            'type' => $type,
            'material_id' => $data['material_id'] ?? null,
            'supplier_id' => $data['supplier_id'] ?? null,
            'unit_id' => $data['unit_id'] ?? null,
            'qty' => $data['qty'] ?? null,
            'unit_cost' => $data['unit_cost'] ?? null,
            'qty_base' => $qtyBase,
            'unit_cost_base' => $unitCostBase,
            'amount' => $amount ?? 0,
            'payment_method' => $data['payment_method'] ?? 'cash',
            'expense_date' => $data['expense_date'] ?? now()->format('Y-m-d'),
            'notes' => $data['notes'] ?? null,
        ];
    }

    protected function resolveConversion(?Material $material, ?int $unitId): float
    {
        if (!$material) {
            return 1;
        }

        if ($unitId && $unitId === $material->unit_id) {
            return 1;
        }

        if ($unitId && $material->purchase_unit_id && $unitId === $material->purchase_unit_id) {
            return max(1, (float) $material->conversion_qty);
        }

        return 1;
    }

    protected function updateMaterialCost(int $materialId, float $qtyBase, float $unitCostBase): void
    {
        if ($qtyBase <= 0 || $unitCostBase <= 0) {
            return;
        }

        $material = Material::find($materialId);
        if (!$material) {
            return;
        }

        $currentQty = $this->currentStock($materialId);
        $currentCost = (float) ($material->cost_price ?? 0);
        $denom = $currentQty + $qtyBase;
        if ($denom <= 0) {
            return;
        }

        $newAvg = (($currentQty * $currentCost) + ($qtyBase * $unitCostBase)) / $denom;
        $material->update(['cost_price' => $newAvg]);
    }

    protected function currentStock(int $materialId): float
    {
        $rows = StockMovement::query()
            ->where('material_id', $materialId)
            ->whereNull('deleted_at')
            ->selectRaw('COALESCE(SUM(CASE WHEN type = \"in\" THEN qty WHEN type = \"out\" THEN -qty WHEN type = \"opname\" THEN qty ELSE 0 END), 0) as saldo')
            ->value('saldo');

        return (float) ($rows ?? 0);
    }
}
