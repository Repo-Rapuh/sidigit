<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class OrderReceiptController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected OrderService $service)
    {
    }

    public function show(Request $request, int $order)
    {
        $this->authorize('order.view');

        $orderModel = $this->service->find($order);
        $payments = $orderModel->payments
            ->sortBy(fn ($payment) => sprintf(
                '%020d-%010d',
                $payment->paid_at?->timestamp ?? 0,
                (int) $payment->id
            ))
            ->values();

        if ($payments->isEmpty()) {
            session()->flash('toast', [
                'message' => 'Belum ada pembayaran untuk dicetak struk.',
                'type' => 'warning',
            ]);

            return redirect()->route('orders.payments.create', ['order' => $orderModel->id]);
        }

        $selectedPaymentId = (int) $request->integer('payment_id');
        $selectedPayment = $selectedPaymentId > 0
            ? $payments->firstWhere('id', $selectedPaymentId)
            : null;
        $selectedPayment ??= $payments->last();

        [$previousPaid, $cumulativePaid] = $this->calculatePaidProgress($payments, (int) $selectedPayment->id);
        $paperWidth = $this->resolvePaperWidth($request->integer('paper', 80));
        $grandTotal = (float) ($orderModel->grand_total ?? 0);
        $paymentAmount = (float) ($selectedPayment->amount ?? 0);
        $remainingBefore = max(0, $grandTotal - $previousPaid);
        $remainingAfter = max(0, $grandTotal - $cumulativePaid);
        $changeAtPayment = max(0, $paymentAmount - $remainingBefore);
        $totalChange = max(0, $cumulativePaid - $grandTotal);

        return view('admin.orders.receipt', [
            'order' => $orderModel,
            'print' => $request->boolean('print'),
            'payments' => $payments,
            'selectedPayment' => $selectedPayment,
            'paperWidth' => $paperWidth,
            'paymentAmount' => $paymentAmount,
            'previousPaid' => $previousPaid,
            'cumulativePaid' => $cumulativePaid,
            'remainingBefore' => $remainingBefore,
            'remainingAfter' => $remainingAfter,
            'changeAtPayment' => $changeAtPayment,
            'totalChange' => $totalChange,
        ]);
    }

    /**
     * @return array{0: float, 1: float}
     */
    protected function calculatePaidProgress(Collection $payments, int $selectedPaymentId): array
    {
        $previousPaid = 0.0;
        $cumulativePaid = 0.0;

        foreach ($payments as $payment) {
            $amount = (float) ($payment->amount ?? 0);
            if ((int) $payment->id === $selectedPaymentId) {
                $cumulativePaid = $previousPaid + $amount;
                break;
            }
            $previousPaid += $amount;
            $cumulativePaid = $previousPaid;
        }

        return [$previousPaid, $cumulativePaid];
    }

    protected function resolvePaperWidth(int $paperWidth): int
    {
        return in_array($paperWidth, [58, 80], true) ? $paperWidth : 80;
    }
}
