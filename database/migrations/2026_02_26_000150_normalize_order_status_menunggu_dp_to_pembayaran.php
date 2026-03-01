<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('orders')
            ->where('status', 'menunggu-dp')
            ->update(['status' => 'pembayaran']);

        DB::table('order_status_logs')
            ->where('status', 'menunggu-dp')
            ->update(['status' => 'pembayaran']);
    }

    public function down(): void
    {
        // no-op: rollback status global berisiko mengubah data operasional yang valid.
    }
};
