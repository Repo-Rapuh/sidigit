<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('orders')
            ->where('status', 'finishing')
            ->update(['status' => 'produksi']);

        DB::table('order_status_logs')
            ->where('status', 'finishing')
            ->update(['status' => 'produksi']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // no-op: rollback status global berisiko mengubah data produksi yang valid.
    }
};

