<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $service)
    {
    }

    public function index(Request $request)
    {
        $summary = $this->service->build([
            'range' => $request->query('range', 'today'),
            'from' => $request->query('from'),
            'to' => $request->query('to'),
        ]);

        return view('dashboard', [
            'summary' => $summary,
            'activeBranchName' => $this->activeBranchName(),
        ]);
    }

    protected function activeBranchName(): ?string
    {
        $activeBranchId = session('active_branch_id');
        if (! empty($activeBranchId)) {
            return Branch::query()->where('id', $activeBranchId)->value('name');
        }

        $userBranchId = auth()->user()?->branch_id;
        if (! empty($userBranchId)) {
            return Branch::query()->where('id', $userBranchId)->value('name');
        }

        return null;
    }
}

