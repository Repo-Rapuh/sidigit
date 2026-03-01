<?php

namespace App\Services;

use App\Models\AccountingAccount;
use App\Models\AccountingJournal;
use App\Models\AccountingJournalLine;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AccountingOverviewService
{
    public function snapshot(?string $startDate = null, ?string $endDate = null): array
    {
        [$startDate, $endDate] = $this->normalizeRange($startDate, $endDate);

        $accounts = AccountingAccount::query()
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type', 'normal_balance']);

        $lineTotals = AccountingJournalLine::query()
            ->join('acc_journals', 'acc_journals.id', '=', 'acc_journal_lines.journal_id')
            ->whereBetween('acc_journals.journal_date', [$startDate, $endDate])
            ->selectRaw('account_id, COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')
            ->groupBy('account_id')
            ->get()
            ->keyBy('account_id');

        $balances = $accounts->map(function (AccountingAccount $account) use ($lineTotals) {
            $totals = $lineTotals->get($account->id);
            $debit = (float) ($totals->total_debit ?? 0);
            $credit = (float) ($totals->total_credit ?? 0);

            $balance = $account->normal_balance === 'credit'
                ? $credit - $debit
                : $debit - $credit;

            return [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'normal_balance' => $account->normal_balance,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => round($balance, 2),
            ];
        });

        $balanceByCode = $balances->keyBy('code');

        $recentJournals = AccountingJournal::query()
            ->with('postedBy:id,name')
            ->whereBetween('journal_date', [$startDate, $endDate])
            ->orderByDesc('journal_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get([
                'id',
                'journal_no',
                'journal_date',
                'description',
                'total_debit',
                'total_credit',
                'posted_by',
            ]);

        return [
            'range' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'cards' => $this->buildCards($balanceByCode),
            'typeSummary' => $this->buildTypeSummary($balances),
            'accountHighlights' => $this->buildHighlights($balanceByCode),
            'recentJournals' => $recentJournals,
        ];
    }

    protected function buildCards(Collection $balanceByCode): array
    {
        $value = fn (string $code) => (float) data_get($balanceByCode->get($code), 'balance', 0);

        return [
            ['label' => 'Kas', 'code' => '1001', 'value' => $value('1001')],
            ['label' => 'Bank', 'code' => '1002', 'value' => $value('1002')],
            ['label' => 'Piutang Usaha', 'code' => '1101', 'value' => $value('1101')],
            ['label' => 'Uang Muka Pelanggan', 'code' => '2003', 'value' => $value('2003')],
            ['label' => 'Hutang Kembalian', 'code' => '2002', 'value' => $value('2002')],
            ['label' => 'Pendapatan', 'code' => '4001', 'value' => $value('4001')],
            ['label' => 'HPP', 'code' => '5001', 'value' => $value('5001')],
            ['label' => 'Beban Operasional', 'code' => '6001', 'value' => $value('6001')],
        ];
    }

    protected function buildTypeSummary(Collection $balances): array
    {
        $sumType = fn (string $type) => (float) $balances
            ->where('type', $type)
            ->sum('balance');

        $asset = $sumType('asset');
        $liability = $sumType('liability');
        $equity = $sumType('equity');
        $revenue = $sumType('revenue');
        $expense = $sumType('expense');

        return [
            'asset' => $asset,
            'liability' => $liability,
            'equity' => $equity,
            'revenue' => $revenue,
            'expense' => $expense,
            'estimated_profit' => $revenue - $expense,
        ];
    }

    protected function buildHighlights(Collection $balanceByCode): array
    {
        $pick = function (string $code, string $label) use ($balanceByCode): array {
            $row = $balanceByCode->get($code);
            return [
                'label' => $label,
                'code' => $code,
                'balance' => (float) data_get($row, 'balance', 0),
                'debit' => (float) data_get($row, 'debit', 0),
                'credit' => (float) data_get($row, 'credit', 0),
            ];
        };

        return [
            $pick('1001', 'Kas'),
            $pick('1002', 'Bank'),
            $pick('1101', 'Piutang Usaha'),
            $pick('2003', 'Uang Muka Pelanggan'),
            $pick('4001', 'Pendapatan Penjualan'),
            $pick('5001', 'HPP'),
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
