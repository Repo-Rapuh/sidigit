<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('production_jobs', 'stage')) {
                $table->string('stage', 24)->default('produksi')->after('order_item_id');
            }

            if (!Schema::hasColumn('production_jobs', 'claimed_by')) {
                $table->unsignedBigInteger('claimed_by')->nullable()->after('assigned_role_id');
                $table->foreign('claimed_by')->references('id')->on('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('production_jobs', 'claimed_at')) {
                $table->timestamp('claimed_at')->nullable()->after('claimed_by');
            }

            $table->index(['branch_id', 'stage', 'status'], 'production_jobs_branch_stage_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('production_jobs', function (Blueprint $table) {
            $table->dropIndex('production_jobs_branch_stage_status_idx');

            if (Schema::hasColumn('production_jobs', 'claimed_by')) {
                $table->dropForeign(['claimed_by']);
                $table->dropColumn('claimed_by');
            }

            if (Schema::hasColumn('production_jobs', 'claimed_at')) {
                $table->dropColumn('claimed_at');
            }

            if (Schema::hasColumn('production_jobs', 'stage')) {
                $table->dropColumn('stage');
            }
        });
    }
};
