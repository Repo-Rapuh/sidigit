<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mst_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128);
            $table->string('on_behalf', 128)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('industry', 128);
            $table->string('phone_number', 32);
            $table->string('email', 128)->nullable();
            $table->string('rekening_number', 128)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mst_suppliers');
    }
};
