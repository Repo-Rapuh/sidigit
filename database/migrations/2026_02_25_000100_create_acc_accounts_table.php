<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acc_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('code', 32);
            $table->string('name', 160);
            $table->string('type', 32);
            $table->string('normal_balance', 8);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['branch_id', 'code'], 'acc_accounts_branch_code_unique');
            $table->index(['branch_id', 'type'], 'acc_accounts_branch_type_index');
            $table->index(['branch_id', 'is_active'], 'acc_accounts_branch_active_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acc_accounts');
    }
};

