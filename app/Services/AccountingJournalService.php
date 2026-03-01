<?php

namespace App\Services;

use App\Models\AccountingAccount;
use App\Models\AccountingJournal;
use App\Repositories\AccountingJournalRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountingJournalService
{
    protected AccountingJournalRepository $repository;

    public function __construct(AccountingJournalRepository $repository)
    {
        $this->repository = $repository;
    }

    public function query(): Builder
    {
        return $this->repository->query()
            ->with(['lines.account', 'postedBy:id,name']);
    }

    public function recent(int $limit = 15): Collection
    {
        $branchId = $this->resolveBranchId();

        return $this->query()
            ->where('branch_id', $branchId)
            ->orderByDesc('journal_date')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function accountOptions(): Collection
    {
        $branchId = $this->resolveBranchId();

        return AccountingAccount::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type'])
            ->map(fn (AccountingAccount $account) => [
                'id' => $account->id,
                'label' => "{$account->code} - {$account->name}",
                'type' => $account->type,
            ]);
    }

    public function store(array $header, array $lines): AccountingJournal
    {
        $branchId = $this->resolveBranchId();
        $journalDate = Carbon::parse(Arr::get($header, 'journal_date', now()->toDateString()))->toDateString();

        $sanitizedLines = $this->sanitizeLines($lines);
        $this->validateLines($sanitizedLines);
        $this->ensureAccountsExist($sanitizedLines, $branchId);

        $totalDebit = round($sanitizedLines->sum('debit'), 2);
        $totalCredit = round($sanitizedLines->sum('credit'), 2);

        if ($totalDebit <= 0 || $totalCredit <= 0) {
            throw ValidationException::withMessages([
                'lines' => 'Total debit dan kredit harus lebih dari 0.',
            ]);
        }

        if (abs($totalDebit - $totalCredit) > 0.009) {
            throw ValidationException::withMessages([
                'lines' => 'Jurnal tidak balance. Total debit dan kredit harus sama.',
            ]);
        }

        return DB::transaction(function () use ($branchId, $journalDate, $header, $sanitizedLines, $totalDebit, $totalCredit) {
            $journal = $this->repository->create([
                'branch_id' => $branchId,
                'journal_no' => $this->generateJournalNo($journalDate, $branchId),
                'journal_date' => $journalDate,
                'description' => trim((string) Arr::get($header, 'description', '')),
                'source_type' => Arr::get($header, 'source_type'),
                'source_id' => Arr::get($header, 'source_id'),
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'posted_by' => auth()->id(),
            ]);

            $journal->lines()->createMany(
                $sanitizedLines
                    ->map(fn (array $line) => [
                        'branch_id' => $branchId,
                        'account_id' => $line['account_id'],
                        'description' => $line['description'],
                        'debit' => $line['debit'],
                        'credit' => $line['credit'],
                    ])
                    ->all()
            );

            return $journal->load(['lines.account', 'postedBy:id,name']);
        });
    }

    protected function sanitizeLines(array $lines): Collection
    {
        return collect($lines)
            ->map(function ($line) {
                $accountId = Arr::get($line, 'account_id');
                $debit = (float) Arr::get($line, 'debit', 0);
                $credit = (float) Arr::get($line, 'credit', 0);
                $description = trim((string) Arr::get($line, 'description', ''));

                return [
                    'account_id' => !empty($accountId) ? (int) $accountId : null,
                    'debit' => round(max(0, $debit), 2),
                    'credit' => round(max(0, $credit), 2),
                    'description' => $description !== '' ? $description : null,
                ];
            })
            ->filter(function (array $line) {
                $hasAmount = ($line['debit'] > 0 || $line['credit'] > 0);
                return $line['account_id'] !== null || $hasAmount || filled($line['description']);
            })
            ->values();
    }

    protected function validateLines(Collection $lines): void
    {
        if ($lines->count() < 2) {
            throw ValidationException::withMessages([
                'lines' => 'Minimal 2 baris jurnal wajib diisi.',
            ]);
        }

        foreach ($lines as $index => $line) {
            $lineNo = $index + 1;
            if (empty($line['account_id'])) {
                throw ValidationException::withMessages([
                    "lines.{$index}.account_id" => "Akun pada baris ke-{$lineNo} wajib dipilih.",
                ]);
            }

            $debit = (float) $line['debit'];
            $credit = (float) $line['credit'];

            if ($debit > 0 && $credit > 0) {
                throw ValidationException::withMessages([
                    "lines.{$index}.debit" => "Baris ke-{$lineNo} tidak boleh mengisi debit dan kredit sekaligus.",
                ]);
            }

            if ($debit <= 0 && $credit <= 0) {
                throw ValidationException::withMessages([
                    "lines.{$index}.debit" => "Baris ke-{$lineNo} harus memiliki nominal debit atau kredit.",
                ]);
            }
        }
    }

    protected function ensureAccountsExist(Collection $lines, int $branchId): void
    {
        $accountIds = $lines->pluck('account_id')->filter()->unique()->values();
        $existingCount = AccountingAccount::query()
            ->withoutGlobalScopes()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->whereIn('id', $accountIds)
            ->count();

        if ($existingCount !== $accountIds->count()) {
            throw ValidationException::withMessages([
                'lines' => 'Sebagian akun pada baris jurnal tidak valid atau nonaktif.',
            ]);
        }
    }

    protected function generateJournalNo(string $journalDate, int $branchId): string
    {
        $datePrefix = 'JU' . Carbon::parse($journalDate)->format('Ymd');
        $lastNo = AccountingJournal::query()
            ->withoutGlobalScopes()
            ->where('branch_id', $branchId)
            ->whereDate('journal_date', $journalDate)
            ->where('journal_no', 'like', $datePrefix . '-%')
            ->orderByDesc('id')
            ->value('journal_no');

        $nextSequence = 1;
        if (filled($lastNo) && str_contains((string) $lastNo, '-')) {
            $lastSequence = (int) ((string) str($lastNo)->afterLast('-'));
            $nextSequence = $lastSequence + 1;
        }

        return $datePrefix . '-' . str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }

    protected function resolveBranchId(): int
    {
        $activeBranchId = session('active_branch_id');
        if (!empty($activeBranchId)) {
            return (int) $activeBranchId;
        }

        $userBranchId = auth()->user()?->branch_id;
        if (!empty($userBranchId)) {
            return (int) $userBranchId;
        }

        throw ValidationException::withMessages([
            'general' => 'Cabang aktif tidak ditemukan. Pilih cabang terlebih dahulu.',
        ]);
    }
}
