<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_connectors', function (Blueprint $table) {
            $table->string('connector_id')->primary();
            $table->string('product_id');
            $table->string('connector_type');
            $table->decimal('power_w', 8, 1)->nullable();

            $table->foreign('product_id')->references('product_id')->on('products')->cascadeOnDelete();
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->string('image_id')->primary();
            $table->string('product_id');
            $table->string('name')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->string('main_image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('power_w', 8, 1)->nullable();

            $table->foreign('product_id')->references('product_id')->on('products')->cascadeOnDelete();
        });

        Schema::create('motherboard_slots', function (Blueprint $table) {
            $table->id();
            $table->string('product_id');
            $table->string('slot_type');
            $table->integer('count')->default(0);

            $table->foreign('product_id')->references('product_id')->on('products')->cascadeOnDelete();
        });

        Schema::create('case_radiator_supports', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('product_id');
            $table->integer('size_mm');
            $table->decimal('power_w', 8, 1)->nullable();

            $table->foreign('product_id')->references('product_id')->on('products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_radiator_supports');
        Schema::dropIfExists('motherboard_slots');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('product_connectors');
    }
};
