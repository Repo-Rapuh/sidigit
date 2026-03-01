<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acc_journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('acc_journals')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('acc_accounts')->restrictOnDelete();
            $table->string('description', 255)->nullable();
            $table->decimal('debit', 16, 2)->default(0);
            $table->decimal('credit', 16, 2)->default(0);
            $table->timestamps();

            $table->index(['journal_id', 'account_id'], 'acc_journal_lines_journal_account_index');
            $table->index(['branch_id', 'account_id'], 'acc_journal_lines_branch_account_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acc_journal_lines');
    }
};

