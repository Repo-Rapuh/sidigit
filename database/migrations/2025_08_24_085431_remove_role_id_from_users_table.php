<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Cek dulu apakah kolom 'role_id' ada di tabel 'users'
            if (Schema::hasColumn('users', 'role_id')) {
                // Hapus foreign key constraint terlebih dahulu
                // Nama constraint bisa bervariasi, jadi drop berdasarkan nama kolom lebih aman
                $table->dropForeign(['role_id']);

                // Hapus kolomnya
                $table->dropColumn('role_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Jika di-rollback, buat kembali kolomnya
            $table->foreignId('role_id')->after('id')->constrained('roles')->cascadeOnDelete();
        });
    }
};
