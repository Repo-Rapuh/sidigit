<?php

namespace App\Http\Controllers\Tracking;

use App\Http\Controllers\Controller;
use App\Services\OrderTrackingService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    public function show(string $id_order_encrypted, OrderTrackingService $trackingService): View
    {
        try {
            $order = $trackingService->findByEncryptedId($id_order_encrypted);
        } catch (ModelNotFoundException) {
            abort(404);
        }

        return view('public.orders.track', [
            'order' => $order,
            'timeline' => $trackingService->timeline($order),
            'currentStatusLabel' => $trackingService->statusLabel($order->status),
        ]);
    }
}

