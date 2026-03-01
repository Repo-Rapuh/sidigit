<?php

namespace App\Services;

use App\Models\AccountingAccount;
use App\Repositories\AccountingAccountRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountingAccountService
{
    protected AccountingAccountRepository $repository;

    public function __construct(AccountingAccountRepository $repository)
    {
        $this->repository = $repository;
    }

    public function query(): Builder
    {
        return $this->repository->query();
    }

    public function find(int $id): AccountingAccount
    {
        return $this->repository->findOrFail($id);
    }

    public function store(array $data): AccountingAccount
    {
        $payload = $this->normalizePayload($data);
        $this->ensureUniqueCode($payload['code']);

        return $this->repository->create($payload);
    }

    public function update(int $id, array $data): AccountingAccount
    {
        $account = $this->repository->findOrFail($id);
        $payload = $this->normalizePayload($data, $account->branch_id);
        $this->ensureUniqueCode($payload['code'], $account->id, $account->branch_id);

        return $this->repository->update($account, $payload);
    }

    public function destroy(int $id): void
    {
        $account = $this->repository->findOrFail($id);

        $hasJournalLines = $account->journalLines()->exists();
        if ($hasJournalLines) {
            throw ValidationException::withMessages([
                'general' => 'Akun tidak bisa dihapus karena sudah dipakai pada jurnal.',
            ]);
        }

        $this->repository->delete($account);
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        DB::transaction(function () use ($ids) {
            $accounts = $this->repository->query()->whereIn('id', $ids)->get();

            foreach ($accounts as $account) {
                if ($account->journalLines()->exists()) {
                    throw ValidationException::withMessages([
                        'general' => 'Sebagian akun tidak bisa dihapus karena sudah dipakai pada jurnal.',
                    ]);
                }
            }

            $this->repository->query()->whereIn('id', $ids)->delete();
        });
    }

    public function accountTypeOptions(): array
    {
        return [
            ['value' => 'asset', 'label' => 'Asset'],
            ['value' => 'liability', 'label' => 'Liability'],
            ['value' => 'equity', 'label' => 'Equity'],
            ['value' => 'revenue', 'label' => 'Revenue'],
            ['value' => 'expense', 'label' => 'Expense'],
        ];
    }

    protected function normalizePayload(array $data, ?int $branchId = null): array
    {
        $type = strtolower(trim((string) Arr::get($data, 'type', 'asset')));
        $normalBalance = strtolower(trim((string) Arr::get($data, 'normal_balance', $this->defaultNormalBalance($type))));
        $code = strtoupper(trim((string) Arr::get($data, 'code', '')));

        if (!in_array($type, ['asset', 'liability', 'equity', 'revenue', 'expense'], true)) {
            throw ValidationException::withMessages([
                'form.type' => 'Tipe akun tidak valid.',
            ]);
        }

        if (!in_array($normalBalance, ['debit', 'credit'], true)) {
            throw ValidationException::withMessages([
                'form.normal_balance' => 'Saldo normal tidak valid.',
            ]);
        }

        return [
            'branch_id' => $branchId ?? $this->resolveBranchId(),
            'code' => $code,
            'name' => trim((string) Arr::get($data, 'name', '')),
            'type' => $type,
            'normal_balance' => $normalBalance,
            'is_active' => (bool) Arr::get($data, 'is_active', true),
            'notes' => Arr::get($data, 'notes'),
        ];
    }

    protected function ensureUniqueCode(string $code, ?int $ignoreId = null, ?int $branchId = null): void
    {
        $branchId = $branchId ?? $this->resolveBranchId();

        $query = $this->repository->query()
            ->withoutGlobalScopes()
            ->where('branch_id', $branchId)
            ->whereRaw('UPPER(code) = ?', [strtoupper($code)]);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'form.code' => 'Kode akun sudah dipakai di cabang ini.',
            ]);
        }
    }

    protected function defaultNormalBalance(string $type): string
    {
        return in_array($type, ['liability', 'equity', 'revenue'], true)
            ? 'credit'
            : 'debit';
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

