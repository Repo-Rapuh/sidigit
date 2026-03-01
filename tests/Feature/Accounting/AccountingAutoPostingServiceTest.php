<?php

namespace Tests\Feature\Accounting;

use App\Models\AccountingAccount;
use App\Models\AccountingJournal;
use App\Models\Order;
use App\Models\Payment;
use App\Services\AccountingAutoPostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AccountingAutoPostingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_payment_generates_next_journal_number_without_stringable_cast_error(): void
    {
        $branchId = $this->ensureBranchId();
        $journalDate = '2026-02-26';

        $this->createAccount($branchId, '1001', 'Kas', 'asset', 'debit');
        $this->createAccount($branchId, '2003', 'Uang Muka Pelanggan', 'liability', 'credit');

        AccountingJournal::query()->create([
            'branch_id' => $branchId,
            'journal_no' => 'AUTO20260226-0001',
            'journal_date' => $journalDate,
            'description' => 'Seed journal',
            'source_type' => 'seed',
            'source_id' => 999,
            'total_debit' => 100000,
            'total_credit' => 100000,
        ]);

        $order = Order::query()->create([
            'order_no' => 'ORD-TEST-' . now()->format('YmdHisv'),
            'branch_id' => $branchId,
            'status' => 'pembayaran',
            'order_date' => $journalDate,
            'grand_total' => 100000,
            'paid_amount' => 0,
            'payment_status' => 'unpaid',
        ]);

        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'branch_id' => $branchId,
            'amount' => 50000,
            'method' => 'cash',
            'paid_at' => $journalDate . ' 10:00:00',
            'notes' => 'Termin 1',
        ]);

        app(AccountingAutoPostingService::class)->postPayment($order, $payment, 50000);

        $journal = AccountingJournal::query()
            ->where('source_type', 'payment')
            ->where('source_id', $payment->id)
            ->first();

        $this->assertNotNull($journal);
        $this->assertSame('AUTO20260226-0002', (string) $journal->journal_no);
    }

    protected function createAccount(int $branchId, string $code, string $name, string $type, string $normalBalance): void
    {
        AccountingAccount::query()->updateOrCreate(
            ['branch_id' => $branchId, 'code' => $code],
            [
                'name' => $name,
                'type' => $type,
                'normal_balance' => $normalBalance,
                'is_active' => true,
            ]
        );
    }

    protected function ensureBranchId(): int
    {
        $branchId = DB::table('branches')->value('id');
        if ($branchId) {
            return (int) $branchId;
        }

        return (int) DB::table('branches')->insertGetId([
            'name' => 'Headquarter',
            'address' => 'Alamat',
            'phone' => '000',
            'email' => 'hq@example.test',
            'is_main' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

