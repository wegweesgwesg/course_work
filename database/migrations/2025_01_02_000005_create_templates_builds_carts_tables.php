<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->string('template_id')->primary();
            $table->string('name');
            $table->unsignedBigInteger('author_user_id')->nullable();
            $table->boolean('is_public')->default(false);
            $table->decimal('power_w', 8, 1)->nullable();
            $table->timestamps();

            $table->foreign('author_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('template_items', function (Blueprint $table) {
            $table->string('template_item_id')->primary();
            $table->string('template_id');
            $table->string('slot_type');
            $table->string('product_id');
            $table->decimal('power_w', 8, 1)->nullable();

            $table->foreign('template_id')->references('template_id')->on('templates')->cascadeOnDelete();
            $table->foreign('product_id')->references('product_id')->on('products');
        });

        Schema::create('builds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('total_price')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('build_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('build_id');
            $table->string('slot_type');
            $table->string('product_id');
            $table->integer('quantity')->default(1);
            $table->integer('unit_price')->default(0);

            $table->foreign('build_id')->references('id')->on('builds')->cascadeOnDelete();
            $table->foreign('product_id')->references('product_id')->on('products');
        });

        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('build_id')->nullable();
            $table->integer('quantity')->default(1);

            $table->foreign('cart_id')->references('id')->on('carts')->cascadeOnDelete();
            $table->foreign('build_id')->references('id')->on('builds')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('build_items');
        Schema::dropIfExists('builds');
        Schema::dropIfExists('template_items');
        Schema::dropIfExists('templates');
    }
};
