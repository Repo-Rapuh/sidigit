<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acc_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('journal_no', 64);
            $table->date('journal_date');
            $table->text('description')->nullable();
            $table->string('source_type', 64)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->decimal('total_debit', 16, 2)->default(0);
            $table->decimal('total_credit', 16, 2)->default(0);
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['branch_id', 'journal_no'], 'acc_journals_branch_no_unique');
            $table->index(['branch_id', 'journal_date'], 'acc_journals_branch_date_index');
            $table->index(['source_type', 'source_id'], 'acc_journals_source_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acc_journals');
    }
};

