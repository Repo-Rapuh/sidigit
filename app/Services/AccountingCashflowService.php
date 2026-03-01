<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AccountingCashflowService
{
    public function snapshot(
        ?string $startDate = null,
        ?string $endDate = null,
        string $source = 'all',
        string $method = 'all'
    ): array {
        [$startDate, $endDate] = $this->normalizeRange($startDate, $endDate);

        $openingBalance = $this->openingBalance($startDate, $source, $method);
        $entries = $this->entries($startDate, $endDate, $source, $method);
        $rows = $this->attachRunningBalance($entries, $openingBalance);

        $totalIn = (float) $rows->sum('cash_in');
        $totalOut = (float) $rows->sum('cash_out');
        $net = $totalIn - $totalOut;
        $closingBalance = $openingBalance + $net;

        return [
            'range' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => [
                'opening_balance' => $openingBalance,
                'cash_in' => $totalIn,
                'cash_out' => $totalOut,
                'net' => $net,
                'closing_balance' => $closingBalance,
            ],
            'rows' => $rows,
        ];
    }

    protected function entries(string $startDate, string $endDate, string $source, string $method): Collection
    {
        $rows = collect();

        if (in_array($source, ['all', 'payment'], true)) {
            $payments = $this->paymentsQuery($method)
                ->with('order:id,order_no')
                ->whereDate('paid_at', '>=', $startDate)
                ->whereDate('paid_at', '<=', $endDate)
                ->orderBy('paid_at')
                ->orderBy('id')
                ->get();

            $paymentRows = $payments->map(function (Payment $payment) {
                $paidAt = $payment->paid_at ? Carbon::parse($payment->paid_at) : now();
                $orderNo = $payment->order?->order_no ?: ('ORDER#' . $payment->order_id);
                $description = 'Pembayaran ' . $orderNo;
                if (!empty($payment->notes)) {
                    $description .= ' - ' . trim((string) $payment->notes);
                }

                return [
                    'event_at' => $paidAt,
                    'date' => $paidAt->toDateString(),
                    'source' => 'payment',
                    'reference' => 'PAY#' . $payment->id,
                    'description' => $description,
                    'method' => strtolower((string) ($payment->method ?? 'cash')),
                    'cash_in' => (float) $payment->amount,
                    'cash_out' => 0.0,
                ];
            });

            $rows = $rows->merge($paymentRows);
        }

        if (in_array($source, ['all', 'expense'], true)) {
            $expenses = $this->expensesQuery($method)
                ->whereDate('expense_date', '>=', $startDate)
                ->whereDate('expense_date', '<=', $endDate)
                ->orderBy('expense_date')
                ->orderBy('id')
                ->get();

            $expenseRows = $expenses->map(function (Expense $expense) {
                $expenseDate = $expense->expense_date ? Carbon::parse($expense->expense_date) : now();
                $typeLabel = $expense->type === 'material' ? 'Expense Bahan' : 'Expense Umum';
                $description = $typeLabel;
                if (!empty($expense->notes)) {
                    $description .= ' - ' . trim((string) $expense->notes);
                }

                return [
                    'event_at' => $expenseDate->copy()->setTime(23, 59, 59),
                    'date' => $expenseDate->toDateString(),
                    'source' => 'expense',
                    'reference' => 'EXP#' . $expense->id,
                    'description' => $description,
                    'method' => strtolower((string) ($expense->payment_method ?? 'cash')),
                    'cash_in' => 0.0,
                    'cash_out' => (float) $expense->amount,
                ];
            });

            $rows = $rows->merge($expenseRows);
        }

        return $rows
            ->sortBy([
                ['event_at', 'asc'],
                ['reference', 'asc'],
            ])
            ->values();
    }

    protected function openingBalance(string $startDate, string $source, string $method): float
    {
        $cashIn = 0.0;
        $cashOut = 0.0;

        if (in_array($source, ['all', 'payment'], true)) {
            $cashIn = (float) $this->paymentsQuery($method)
                ->whereDate('paid_at', '<', $startDate)
                ->sum('amount');
        }

        if (in_array($source, ['all', 'expense'], true)) {
            $cashOut = (float) $this->expensesQuery($method)
                ->whereDate('expense_date', '<', $startDate)
                ->sum('amount');
        }

        return $cashIn - $cashOut;
    }

    protected function attachRunningBalance(Collection $rows, float $openingBalance): Collection
    {
        $balance = $openingBalance;

        return $rows->map(function (array $row) use (&$balance) {
            $balance += ((float) $row['cash_in'] - (float) $row['cash_out']);
            $row['balance'] = round($balance, 2);
            return $row;
        });
    }

    protected function paymentsQuery(string $method)
    {
        $query = Payment::query();

        if ($method !== 'all') {
            $query->where('method', $method);
        }

        return $query;
    }

    protected function expensesQuery(string $method)
    {
        $query = Expense::query();

        if ($method !== 'all') {
            $query->where('payment_method', $method);
        }

        return $query;
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

