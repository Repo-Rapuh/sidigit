<?php

namespace App\Livewire\Admin\Reports;

use App\Services\ProductionReportService;
use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Laporan Produksi')]
class ProductionReport extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    protected ProductionReportService $service;

    public string $start_date;
    public string $end_date;

    public function boot(ProductionReportService $service): void
    {
        $this->service = $service;
    }

    public function mount(): void
    {
        $this->authorize('report.production.view');

        $this->start_date = now()->subDays(30)->format('Y-m-d');
        $this->end_date = now()->format('Y-m-d');

        $this->setPageMeta(
            'Laporan Produksi',
            'Ringkasan performa operasional produksi berdasarkan periode.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Laporan Produksi', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.reports.production', $this->service->snapshot(
            $this->start_date,
            $this->end_date
        ));
    }
}

