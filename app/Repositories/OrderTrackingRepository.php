<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Scopes\BranchScope;

class OrderTrackingRepository
{
    public function findForPublicTracking(int $orderId): ?Order
    {
        return Order::query()
            ->withoutGlobalScope(BranchScope::class)
            ->with([
                'customer' => fn ($query) => $query->withoutGlobalScope(BranchScope::class),
                'statusLogs' => fn ($query) => $query->with('changedByUser:id,name')->orderBy('created_at'),
            ])
            ->find($orderId);
    }
}

