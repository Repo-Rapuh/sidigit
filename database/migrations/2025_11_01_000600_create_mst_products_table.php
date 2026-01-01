<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mst_products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 64)->unique();
            $table->string('name', 128);
            $table->decimal('base_price', 14, 2)->nullable(true);
            $table->decimal('sale_price', 14, 2)->nullable(true);
            $table->decimal('length_cm', 10, 2)->nullable(true);
            $table->decimal('width_cm', 10, 2)->nullable(true);
            $table->foreignId('unit_id')->constrained('mst_units')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('mst_categories')->cascadeOnDelete();
            $table->text('description')->nullable();
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
        Schema::dropIfExists('mst_products');
    }
};
