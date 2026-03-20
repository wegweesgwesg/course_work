<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->string('product_id')->primary();
            $table->string('category_id');
            $table->string('sku')->nullable();
            $table->string('name');
            $table->integer('price')->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->text('description')->nullable();
            $table->string('main_image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('power_w', 8, 1)->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('category_id')->on('categories');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
