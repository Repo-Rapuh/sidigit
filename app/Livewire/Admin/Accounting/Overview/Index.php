<?php

namespace App\Livewire\Admin\Accounting\Overview;

use App\Services\AccountingOverviewService;
use App\Traits\WithPageMeta;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard Akuntansi')]
class Index extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    protected AccountingOverviewService $service;
    public string $period = 'monthly';
    public string $start_date = '';
    public string $end_date = '';

    public function boot(AccountingOverviewService $service): void
    {
        $this->service = $service;
    }

    public function mount(): void
    {
        $this->authorize('accounting-overview.view');
        $this->applyPeriodDefaults();

        $this->setPageMeta(
            'Dashboard Akuntansi',
            'Ringkasan posisi akun utama untuk operasional harian.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Akuntansi', 'current' => true],
                ['label' => 'Dashboard Akuntansi', 'current' => true],
            ]
        );
    }

    public function updatedPeriod(string $value): void
    {
        if (!in_array($value, ['daily', 'monthly', 'custom'], true)) {
            $this->period = 'monthly';
        }

        if ($this->period !== 'custom') {
            $this->applyPeriodDefaults();
        }
    }

    public function updatedStartDate(string $value): void
    {
        if ($this->period !== 'custom') {
            return;
        }

        if (filled($this->end_date) && Carbon::parse($value)->greaterThan(Carbon::parse($this->end_date))) {
            $this->end_date = $value;
        }
    }

    public function updatedEndDate(string $value): void
    {
        if ($this->period !== 'custom') {
            return;
        }

        if (filled($this->start_date) && Carbon::parse($value)->lessThan(Carbon::parse($this->start_date))) {
            $this->start_date = $value;
        }
    }

    public function render()
    {
        return view('livewire.admin.accounting.overview.index', $this->service->snapshot(
            $this->start_date,
            $this->end_date
        ));
    }

    protected function applyPeriodDefaults(): void
    {
        if ($this->period === 'daily') {
            $today = now()->toDateString();
            $this->start_date = $today;
            $this->end_date = $today;
            return;
        }

        $this->start_date = now()->startOfMonth()->toDateString();
        $this->end_date = now()->endOfMonth()->toDateString();
    }
}
