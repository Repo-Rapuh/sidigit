<?php

namespace App\Services;

class FinancialReportService
{
    protected AccountingCashflowService $cashflowService;
    protected AccountingOverviewService $overviewService;

    public function __construct(
        AccountingCashflowService $cashflowService,
        AccountingOverviewService $overviewService
    ) {
        $this->cashflowService = $cashflowService;
        $this->overviewService = $overviewService;
    }

    public function snapshot(
        ?string $startDate = null,
        ?string $endDate = null,
        string $source = 'all',
        string $method = 'all'
    ): array {
        $cashflow = $this->cashflowService->snapshot($startDate, $endDate, $source, $method);
        $overview = $this->overviewService->snapshot($cashflow['range']['start_date'], $cashflow['range']['end_date']);

        $cardsByCode = collect($overview['cards'] ?? [])->keyBy('code');
        $value = fn (string $code): float => (float) data_get($cardsByCode->get($code), 'value', 0);

        $revenue = $value('4001');
        $cogs = $value('5001');
        $operatingExpense = $value('6001');
        $grossProfit = $revenue - $cogs;
        $netProfit = $grossProfit - $operatingExpense;

        $assets = [
            ['label' => 'Kas', 'code' => '1001', 'value' => $value('1001')],
            ['label' => 'Bank', 'code' => '1002', 'value' => $value('1002')],
            ['label' => 'Piutang Usaha', 'code' => '1101', 'value' => $value('1101')],
            ['label' => 'Persediaan Bahan', 'code' => '1201', 'value' => $value('1201')],
        ];

        $liabilities = [
            ['label' => 'Uang Muka Pelanggan', 'code' => '2003', 'value' => $value('2003')],
            ['label' => 'Hutang Kembalian', 'code' => '2002', 'value' => $value('2002')],
        ];

        $assetTotal = (float) collect($assets)->sum('value');
        $liabilityTotal = (float) collect($liabilities)->sum('value');
        $equity = (float) data_get($overview, 'typeSummary.equity', 0);
        $currentProfit = (float) data_get($overview, 'typeSummary.estimated_profit', 0);
        $totalLiabilityAndEquity = $liabilityTotal + $equity + $currentProfit;

        return [
            'range' => $cashflow['range'],
            'cashflow_summary' => $cashflow['summary'],
            'cashflow_rows' => $cashflow['rows'],
            'profit_loss' => [
                'revenue' => $revenue,
                'cogs' => $cogs,
                'gross_profit' => $grossProfit,
                'operating_expense' => $operatingExpense,
                'net_profit' => $netProfit,
            ],
            'balance_sheet' => [
                'assets' => $assets,
                'asset_total' => $assetTotal,
                'liabilities' => $liabilities,
                'liability_total' => $liabilityTotal,
                'equity' => $equity,
                'current_profit' => $currentProfit,
                'total_liability_and_equity' => $totalLiabilityAndEquity,
                'delta' => $assetTotal - $totalLiabilityAndEquity,
            ],
        ];
    }
}

