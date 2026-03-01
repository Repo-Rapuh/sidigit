<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\OrderTrackingRepository;
use App\Support\OrderTrackingToken;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class OrderTrackingService
{
    protected OrderTrackingRepository $repository;

    public function __construct(OrderTrackingRepository $repository)
    {
        $this->repository = $repository;
    }

    public function findByEncryptedId(string $idOrderEncrypted): Order
    {
        $orderId = OrderTrackingToken::decode($idOrderEncrypted);

        if ($orderId === null) {
            throw (new ModelNotFoundException())->setModel(Order::class);
        }

        $order = $this->repository->findForPublicTracking($orderId);

        if (!$order) {
            throw (new ModelNotFoundException())->setModel(Order::class, [$orderId]);
        }

        return $order;
    }

    public function statusLabel(?string $status): string
    {
        $status = (string) $status;

        $labels = [
            'draft' => 'Draft',
            'quotation' => 'Quotation',
            'approval' => 'Approval Customer',
            'pembayaran' => 'Pembayaran',
            'desain' => 'Desain',
            'produksi' => 'Produksi',
            'qc' => 'QC',
            'siap' => 'Siap Diambil / Dikirim',
            'diambil' => 'Diambil',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
        ];

        return $labels[$status] ?? Str::of($status)->replace(['-', '_'], ' ')->title()->toString();
    }

    public function timeline(Order $order): Collection
    {
        $logs = $order->statusLogs->map(function ($log) {
            return [
                'status' => $log->status,
                'status_label' => $this->statusLabel($log->status),
                'note' => $log->note,
                'changed_by' => $log->changedByUser?->name,
                'created_at' => $log->created_at,
            ];
        });

        if ($logs->isNotEmpty()) {
            return $logs;
        }

        return collect([
            [
                'status' => $order->status,
                'status_label' => $this->statusLabel($order->status),
                'note' => 'Order dibuat.',
                'changed_by' => null,
                'created_at' => $order->created_at,
            ],
        ]);
    }
}
