<?php

namespace Database\Seeders;

use App\Models\AccountingAccount;
use App\Models\Branch;
use Illuminate\Database\Seeder;

class AccountingAccountSeeder extends Seeder
{
    public function run(): void
    {
        $mainBranch = Branch::query()
            ->where('is_main', true)
            ->first()
            ?? Branch::query()->orderBy('id')->first();

        if (!$mainBranch) {
            return;
        }

        $defaultAccounts = [
            ['code' => '1001', 'name' => 'Kas', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1002', 'name' => 'Bank', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1101', 'name' => 'Piutang Usaha', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1201', 'name' => 'Persediaan Bahan', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '2001', 'name' => 'Hutang Usaha', 'type' => 'liability', 'normal_balance' => 'credit'],
            ['code' => '2002', 'name' => 'Hutang Kembalian Pelanggan', 'type' => 'liability', 'normal_balance' => 'credit'],
            ['code' => '2003', 'name' => 'Uang Muka Pelanggan', 'type' => 'liability', 'normal_balance' => 'credit'],
            ['code' => '3001', 'name' => 'Modal Pemilik', 'type' => 'equity', 'normal_balance' => 'credit'],
            ['code' => '4001', 'name' => 'Pendapatan Penjualan', 'type' => 'revenue', 'normal_balance' => 'credit'],
            ['code' => '5001', 'name' => 'Harga Pokok Penjualan', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '6001', 'name' => 'Beban Operasional', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '6002', 'name' => 'Beban Gaji', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '6003', 'name' => 'Beban Utilitas', 'type' => 'expense', 'normal_balance' => 'debit'],
        ];

        foreach ($defaultAccounts as $account) {
            AccountingAccount::query()->updateOrCreate(
                [
                    'branch_id' => $mainBranch->id,
                    'code' => $account['code'],
                ],
                [
                    'name' => $account['name'],
                    'type' => $account['type'],
                    'normal_balance' => $account['normal_balance'],
                    'is_active' => true,
                ]
            );
        }
    }
}
