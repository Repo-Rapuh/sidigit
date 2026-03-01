<?php

namespace App\Services;

use App\Models\ProductionJob;
use App\Models\ProductionJobLog;
use Carbon\Carbon;

class ProductionReportService
{
    public function snapshot(?string $startDate = null, ?string $endDate = null): array
    {
        [$startDate, $endDate] = $this->normalizeRange($startDate, $endDate);

        $incomingJobs = ProductionJob::query()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        $completedJobs = ProductionJob::query()
            ->where('status', ProductionJob::STATUS_SIAP_DIAMBIL)
            ->whereDate('updated_at', '>=', $startDate)
            ->whereDate('updated_at', '<=', $endDate);

        $wipCount = (int) ProductionJob::query()
            ->whereIn('status', [
                ProductionJob::STATUS_ANTRIAN,
                ProductionJob::STATUS_IN_PROGRESS,
                ProductionJob::STATUS_QC,
            ])
            ->count();

        $avgLeadMinutes = (float) (clone $completedJobs)
            ->selectRaw('COALESCE(AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)), 0) as avg_minutes')
            ->value('avg_minutes');

        $onTimeCount = (int) ProductionJob::query()
            ->join('orders', 'orders.id', '=', 'production_jobs.order_id')
            ->where('production_jobs.status', ProductionJob::STATUS_SIAP_DIAMBIL)
            ->whereDate('production_jobs.updated_at', '>=', $startDate)
            ->whereDate('production_jobs.updated_at', '<=', $endDate)
            ->whereNotNull('orders.deadline')
            ->whereRaw('DATE(production_jobs.updated_at) <= orders.deadline')
            ->count();

        $lateCount = (int) ProductionJob::query()
            ->join('orders', 'orders.id', '=', 'production_jobs.order_id')
            ->where('production_jobs.status', ProductionJob::STATUS_SIAP_DIAMBIL)
            ->whereDate('production_jobs.updated_at', '>=', $startDate)
            ->whereDate('production_jobs.updated_at', '<=', $endDate)
            ->whereNotNull('orders.deadline')
            ->whereRaw('DATE(production_jobs.updated_at) > orders.deadline')
            ->count();

        $statusCountsRaw = ProductionJob::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status');

        $statusCounts = collect(ProductionJob::statusOptions())
            ->map(function (string $label, string $status) use ($statusCountsRaw) {
                return [
                    'status' => $status,
                    'label' => $label,
                    'total' => (int) ($statusCountsRaw[$status] ?? 0),
                ];
            })
            ->values()
            ->all();

        $topProducts = ProductionJob::query()
            ->join('order_items', 'order_items.id', '=', 'production_jobs.order_item_id')
            ->join('mst_products', 'mst_products.id', '=', 'order_items.product_id')
            ->whereDate('production_jobs.created_at', '>=', $startDate)
            ->whereDate('production_jobs.created_at', '<=', $endDate)
            ->selectRaw('mst_products.id as product_id, mst_products.name as product_name, COUNT(production_jobs.id) as total_jobs, COALESCE(SUM(order_items.qty), 0) as total_qty')
            ->groupBy('mst_products.id', 'mst_products.name')
            ->orderByDesc('total_jobs')
            ->limit(10)
            ->get();

        $roleWorkloads = ProductionJob::query()
            ->leftJoin('roles', 'roles.id', '=', 'production_jobs.assigned_role_id')
            ->selectRaw('COALESCE(roles.name, "Tanpa Role") as role_name, COUNT(production_jobs.id) as total_jobs')
            ->whereIn('production_jobs.status', [
                ProductionJob::STATUS_ANTRIAN,
                ProductionJob::STATUS_IN_PROGRESS,
                ProductionJob::STATUS_QC,
            ])
            ->groupBy('role_name')
            ->orderByDesc('total_jobs')
            ->get();

        $qcPassCount = (int) ProductionJobLog::query()
            ->where('event', 'qc_pass')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->count();

        $qcFailCount = (int) ProductionJobLog::query()
            ->where('event', 'qc_fail')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->count();

        return [
            'range' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => [
                'incoming_jobs' => (int) (clone $incomingJobs)->count(),
                'completed_jobs' => (int) (clone $completedJobs)->count(),
                'wip_jobs' => $wipCount,
                'qc_pass' => $qcPassCount,
                'qc_fail' => $qcFailCount,
                'avg_lead_hours' => round($avgLeadMinutes / 60, 2),
                'on_time' => $onTimeCount,
                'late' => $lateCount,
            ],
            'status_counts' => $statusCounts,
            'top_products' => $topProducts,
            'role_workloads' => $roleWorkloads,
        ];
    }

    protected function normalizeRange(?string $startDate, ?string $endDate): array
    {
        $start = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate) : now()->endOfMonth();

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end, $start];
        }

        return [$start->toDateString(), $end->toDateString()];
    }
}
