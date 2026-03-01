<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Material;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Payment;
use App\Models\ProductionJob;
use App\Models\ProductionJobLog;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function build(array $filters = []): array
    {
        [$range, $from, $to, $periodStart, $periodEnd] = $this->resolvePeriod($filters);

        $pipelineStatuses = $this->pipelineStatuses();
        $productionStatuses = $this->productionStatuses();
        $stockBalanceSubquery = $this->stockBalanceSubquery();

        $ordersToday = $this->applyOrderPeriod(
            Order::query(),
            now()->copy()->startOfDay(),
            now()->copy()->endOfDay()
        )->count();

        $pipelineCounts = $this->applyOrderPeriod(Order::query(), $periodStart, $periodEnd)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $receivablesTotal = (float) Order::query()
            ->whereNotIn('status', ['dibatalkan', 'selesai'])
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN grand_total > paid_amount THEN (grand_total - paid_amount) ELSE 0 END), 0) as receivables_total'
            )
            ->value('receivables_total');

        $productionActive = ProductionJob::query()
            ->whereIn('status', [
                ProductionJob::STATUS_ANTRIAN,
                ProductionJob::STATUS_IN_PROGRESS,
                ProductionJob::STATUS_QC,
            ])
            ->count();

        $overdueOrders = Order::query()
            ->whereNotIn('status', ['diambil', 'selesai', 'dibatalkan'])
            ->whereNotNull('deadline')
            ->whereDate('deadline', '<', now()->toDateString())
            ->count();

        $lowStockQuery = Material::query()
            ->leftJoinSub($stockBalanceSubquery, 'sb', function ($join) {
                $join->on('sb.material_id', '=', 'mst_materials.id');
            })
            ->where('mst_materials.reorder_level', '>', 0)
            ->whereRaw('COALESCE(sb.stock_balance, 0) <= mst_materials.reorder_level');

        $lowStockCount = (clone $lowStockQuery)->count();

        $revenueToday = (float) Payment::query()
            ->whereBetween('paid_at', [now()->copy()->startOfDay(), now()->copy()->endOfDay()])
            ->sum('amount');

        $incomePeriod = (float) Payment::query()
            ->whereBetween('paid_at', [$periodStart, $periodEnd])
            ->sum('amount');

        $expensePeriod = (float) Expense::query()
            ->whereBetween('expense_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->sum('amount');

        $productionSnapshotCounts = ProductionJob::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $actionItems = Order::query()
            ->with(['customer:id,name'])
            ->where(function (Builder $query) {
                $query->whereIn('status', ['approval', 'pembayaran', 'qc', 'siap'])
                    ->orWhere(function (Builder $overdue) {
                        $overdue->whereNotIn('status', ['diambil', 'selesai', 'dibatalkan'])
                            ->whereNotNull('deadline')
                            ->whereDate('deadline', '<', now()->toDateString());
                    });
            })
            ->orderByRaw(
                'CASE WHEN deadline IS NOT NULL AND deadline < ? THEN 0 ELSE 1 END',
                [now()->toDateString()]
            )
            ->orderBy('deadline')
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get()
            ->map(function (Order $order) {
                $remaining = max(0, (float) ($order->grand_total ?? 0) - (float) ($order->paid_amount ?? 0));

                return [
                    'id' => (int) $order->id,
                    'order_no' => (string) $order->order_no,
                    'customer_name' => (string) ($order->customer?->name ?? '-'),
                    'status' => (string) ($order->status ?? 'draft'),
                    'deadline' => $order->deadline,
                    'is_overdue' => !empty($order->deadline) && Carbon::parse($order->deadline)->isBefore(now()->startOfDay()),
                    'grand_total' => (float) ($order->grand_total ?? 0),
                    'remaining' => $remaining,
                ];
            })
            ->values();

        $urgentProductionJobs = ProductionJob::query()
            ->select('production_jobs.*')
            ->leftJoin('orders as o', function ($join) {
                $join->on('o.id', '=', 'production_jobs.order_id')
                    ->whereNull('o.deleted_at');
            })
            ->with([
                'order:id,order_no,deadline,status,customer_id',
                'order.customer:id,name',
                'orderItem:id,order_id,product_id,qty',
                'orderItem.product:id,name',
                'claimedByUser:id,name',
            ])
            ->whereIn('production_jobs.status', [
                ProductionJob::STATUS_ANTRIAN,
                ProductionJob::STATUS_IN_PROGRESS,
                ProductionJob::STATUS_QC,
            ])
            ->whereNotIn('o.status', ['dibatalkan', 'selesai'])
            ->orderByRaw('CASE WHEN o.deadline IS NULL THEN 1 ELSE 0 END')
            ->orderBy('o.deadline')
            ->orderByRaw("CASE production_jobs.status WHEN 'qc' THEN 0 WHEN 'in_progress' THEN 1 ELSE 2 END")
            ->orderByDesc('production_jobs.updated_at')
            ->limit(8)
            ->get()
            ->map(function (ProductionJob $job) {
                return [
                    'id' => (int) $job->id,
                    'order_no' => (string) ($job->order?->order_no ?? '-'),
                    'product_name' => (string) ($job->orderItem?->product?->name ?? '-'),
                    'qty' => (float) ($job->orderItem?->qty ?? 0),
                    'stage' => (string) ($job->stage ?? ProductionJob::STAGE_PRODUKSI),
                    'status' => (string) ($job->status ?? ProductionJob::STATUS_ANTRIAN),
                    'deadline' => $job->order?->deadline,
                    'customer_name' => (string) ($job->order?->customer?->name ?? '-'),
                    'claimed_by' => (string) ($job->claimedByUser?->name ?? '-'),
                    'updated_at' => $job->updated_at,
                ];
            })
            ->values();

        $topReceivables = Order::query()
            ->with(['customer:id,name'])
            ->whereNotIn('status', ['dibatalkan', 'selesai'])
            ->whereRaw('grand_total > paid_amount')
            ->select('orders.*')
            ->selectRaw('(grand_total - paid_amount) as remaining')
            ->orderByDesc('remaining')
            ->limit(5)
            ->get()
            ->map(function (Order $order) {
                return [
                    'id' => (int) $order->id,
                    'order_no' => (string) $order->order_no,
                    'customer_name' => (string) ($order->customer?->name ?? '-'),
                    'status' => (string) $order->status,
                    'grand_total' => (float) ($order->grand_total ?? 0),
                    'paid_amount' => (float) ($order->paid_amount ?? 0),
                    'remaining' => max(0, (float) ($order->remaining ?? 0)),
                ];
            })
            ->values();

        $lowStockMaterials = (clone $lowStockQuery)
            ->leftJoin('mst_units as u', 'u.id', '=', 'mst_materials.unit_id')
            ->select(
                'mst_materials.id',
                'mst_materials.name',
                'mst_materials.reorder_level',
                'u.name as unit_name'
            )
            ->selectRaw('COALESCE(sb.stock_balance, 0) as stock_balance')
            ->orderBy('stock_balance')
            ->orderBy('mst_materials.name')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                return [
                    'id' => (int) $row->id,
                    'name' => (string) $row->name,
                    'stock_balance' => (float) ($row->stock_balance ?? 0),
                    'reorder_level' => (float) ($row->reorder_level ?? 0),
                    'unit_name' => (string) ($row->unit_name ?? ''),
                ];
            })
            ->values();

        $orderStatusActivities = OrderStatusLog::query()
            ->with(['order:id,order_no', 'changedByUser:id,name'])
            ->whereHas('order')
            ->latest('created_at')
            ->limit(8)
            ->get()
            ->map(function (OrderStatusLog $log) {
                return [
                    'type' => 'order_status',
                    'title' => 'Status Order',
                    'description' => sprintf(
                        '%s → %s',
                        $log->order?->order_no ?? '-',
                        $this->orderStatusLabel((string) $log->status)
                    ),
                    'meta' => (string) ($log->changedByUser?->name ?? 'System'),
                    'timestamp' => $log->created_at,
                ];
            });

        $paymentActivities = Payment::query()
            ->with(['order:id,order_no'])
            ->latest('paid_at')
            ->limit(8)
            ->get()
            ->map(function (Payment $payment) {
                return [
                    'type' => 'payment',
                    'title' => 'Pembayaran',
                    'description' => sprintf(
                        '%s · %s',
                        $payment->order?->order_no ?? '-',
                        $this->paymentMethodLabel((string) ($payment->method ?? 'cash'))
                    ),
                    'meta' => 'Rp ' . number_format((float) ($payment->amount ?? 0), 0, ',', '.'),
                    'timestamp' => $payment->paid_at ?? $payment->created_at,
                ];
            });

        $productionActivities = ProductionJobLog::query()
            ->with(['order:id,order_no', 'changedByUser:id,name'])
            ->latest('created_at')
            ->limit(8)
            ->get()
            ->map(function (ProductionJobLog $log) {
                return [
                    'type' => 'production',
                    'title' => 'Produksi',
                    'description' => sprintf(
                        '%s · %s',
                        $log->order?->order_no ?? '-',
                        $this->productionEventLabel((string) ($log->event ?? 'updated'))
                    ),
                    'meta' => (string) ($log->changedByUser?->name ?? 'System'),
                    'timestamp' => $log->created_at,
                ];
            });

        $recentActivities = $orderStatusActivities
            ->merge($paymentActivities)
            ->merge($productionActivities)
            ->sortByDesc(fn (array $item) => optional($item['timestamp'])->timestamp ?? 0)
            ->take(10)
            ->values();

        return [
            'filters' => [
                'range' => $range,
                'from' => $from,
                'to' => $to,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'label' => $periodStart->format('d/m/Y') . ' - ' . $periodEnd->format('d/m/Y'),
            ],
            'kpis' => [
                'orders_today' => $ordersToday,
                'revenue_today' => $revenueToday,
                'receivables_active' => $receivablesTotal,
                'production_active' => $productionActive,
                'overdue_orders' => $overdueOrders,
                'low_stock_count' => $lowStockCount,
            ],
            'order_pipeline' => collect($pipelineStatuses)->map(function (array $status) use ($pipelineCounts) {
                return [
                    'value' => $status['value'],
                    'label' => $status['label'],
                    'count' => (int) ($pipelineCounts[$status['value']] ?? 0),
                    'tone' => $status['tone'],
                ];
            })->values(),
            'action_items' => $actionItems,
            'production_snapshot' => collect($productionStatuses)->map(function (array $status) use ($productionSnapshotCounts) {
                return [
                    'value' => $status['value'],
                    'label' => $status['label'],
                    'count' => (int) ($productionSnapshotCounts[$status['value']] ?? 0),
                    'tone' => $status['tone'],
                ];
            })->values(),
            'urgent_production_jobs' => $urgentProductionJobs,
            'cashflow' => [
                'income' => $incomePeriod,
                'expense' => $expensePeriod,
                'net' => $incomePeriod - $expensePeriod,
            ],
            'top_receivables' => $topReceivables,
            'low_stock_materials' => $lowStockMaterials,
            'recent_activities' => $recentActivities,
        ];
    }

    protected function resolvePeriod(array $filters): array
    {
        $range = (string) ($filters['range'] ?? 'today');
        $from = $this->normalizeDateInput($filters['from'] ?? null);
        $to = $this->normalizeDateInput($filters['to'] ?? null);

        $now = now();
        $start = $now->copy()->startOfDay();
        $end = $now->copy()->endOfDay();

        if ($range === '7d') {
            $start = $now->copy()->subDays(6)->startOfDay();
            $end = $now->copy()->endOfDay();
        } elseif ($range === 'month') {
            $start = $now->copy()->startOfMonth()->startOfDay();
            $end = $now->copy()->endOfDay();
        } elseif ($range === 'custom' && $from && $to) {
            $start = Carbon::parse($from)->startOfDay();
            $end = Carbon::parse($to)->endOfDay();
        }

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return [$range, $start->toDateString(), $end->toDateString(), $start, $end];
    }

    protected function normalizeDateInput(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function applyOrderPeriod(Builder $query, Carbon $start, Carbon $end): Builder
    {
        $startDate = $start->toDateString();
        $endDate = $end->toDateString();

        return $query->where(function (Builder $period) use ($startDate, $endDate, $start, $end) {
            $period->whereBetween('order_date', [$startDate, $endDate])
                ->orWhere(function (Builder $fallback) use ($start, $end) {
                    $fallback->whereNull('order_date')
                        ->whereBetween('created_at', [$start, $end]);
                });
        });
    }

    protected function stockBalanceSubquery(): Builder
    {
        return StockMovement::query()
            ->select('material_id')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN type = "in" THEN qty WHEN type = "out" THEN -qty WHEN type = "opname" THEN qty ELSE 0 END), 0) as stock_balance'
            )
            ->groupBy('material_id');
    }

    protected function pipelineStatuses(): array
    {
        return [
            ['value' => 'draft', 'label' => 'Draft', 'tone' => 'slate'],
            ['value' => 'quotation', 'label' => 'Quotation', 'tone' => 'amber'],
            ['value' => 'approval', 'label' => 'Approval', 'tone' => 'emerald'],
            ['value' => 'pembayaran', 'label' => 'Pembayaran', 'tone' => 'sky'],
            ['value' => 'desain', 'label' => 'Desain', 'tone' => 'indigo'],
            ['value' => 'produksi', 'label' => 'Produksi', 'tone' => 'blue'],
            ['value' => 'qc', 'label' => 'QC', 'tone' => 'violet'],
            ['value' => 'siap', 'label' => 'Siap', 'tone' => 'green'],
            ['value' => 'diambil', 'label' => 'Diambil', 'tone' => 'teal'],
            ['value' => 'selesai', 'label' => 'Selesai', 'tone' => 'green'],
            ['value' => 'dibatalkan', 'label' => 'Dibatalkan', 'tone' => 'red'],
        ];
    }

    protected function productionStatuses(): array
    {
        return [
            ['value' => ProductionJob::STATUS_ANTRIAN, 'label' => 'Antrian', 'tone' => 'slate'],
            ['value' => ProductionJob::STATUS_IN_PROGRESS, 'label' => 'Produksi', 'tone' => 'blue'],
            ['value' => ProductionJob::STATUS_QC, 'label' => 'QC', 'tone' => 'violet'],
            ['value' => ProductionJob::STATUS_SIAP_DIAMBIL, 'label' => 'Siap Diambil', 'tone' => 'green'],
        ];
    }

    protected function orderStatusLabel(string $status): string
    {
        return collect($this->pipelineStatuses())
            ->firstWhere('value', $status)['label'] ?? ucfirst($status);
    }

    protected function paymentMethodLabel(string $method): string
    {
        return match (strtolower($method)) {
            'cash' => 'Cash',
            'transfer' => 'Transfer',
            'qris' => 'QRIS',
            default => ucfirst($method),
        };
    }

    protected function productionEventLabel(string $event): string
    {
        return match ($event) {
            'created' => 'Job dibuat',
            'claimed' => 'Task diambil',
            'released' => 'Task dilepas',
            'stage_switched' => 'Pindah tahap',
            'in_progress' => 'Mulai produksi',
            'to_qc' => 'Masuk QC',
            'qc_pass' => 'QC lulus',
            'qc_fail' => 'QC gagal',
            default => 'Update produksi',
        };
    }
}
