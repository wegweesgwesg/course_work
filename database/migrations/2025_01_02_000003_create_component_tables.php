<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cpus', function (Blueprint $table) {
            $table->string('product_id')->primary();
            $table->string('socket')->nullable();
            $table->integer('tdp_w')->nullable();
            $table->integer('max_mem_speed')->nullable();
            $table->string('brand')->nullable();
            $table->integer('cores')->nullable();
            $table->integer('threads')->nullable();
            $table->string('base_clock')->nullable();
            $table->string('boost_clock')->nullable();
            $table->string('integrated_graphics')->nullable();
            $table->integer('lithography_nm')->nullable();
            $table->integer('cache_mb')->nullable();
            $table->decimal('power_w', 8, 1)->nullable();

            $table->foreign('product_id')->references('product_id')->on('products')->cascadeOnDelete();
        });

        Schema::create('motherboards', function (Blueprint $table) {
            $table->string('product_id')->primary();
            $table->string('socket')->nullable();
            $table->string('form_factor')->nullable();
            $table->integer('ram_slots')->nullable();
            $table->integer('max_ram')->nullable();
            $table->integer('ram_speed_max')->nullable();
            $table->integer('m2_slots')->nullable();
            $table->integer('pcie_version')->nullable();
            $table->integer('cpu_fan_headers')->nullable();
            $table->integer('sata_ports')->nullable();
            $table->string('chipset')->nullable();
            $table->string('brand')->nullable();
            $table->decimal('power_w', 8, 1)->nullable();

            $table->foreign('product_id')->references('product_id')->on('products')->cascadeOnDelete();
        });

        Schema::create('rams', function (Blueprint $table) {
            $table->string('product_id')->primary();
            $table->string('ram_type')->nullable();
            $table->integer('size_gb')->nullable();
            $table->integer('speed_mhz')->nullable();
            $table->decimal('power_w', 8, 1)->nullable();

            $table->foreign('product_id')->references('product_id')->on('products')->cascadeOnDelete();
        });

        Schema::create('gpus', function (Blueprint $table) {
            $table->string('product_id')->primary();
            $table->integer('power_draw_w')->nullable();
            $table->integer('length_mm')->nullable();
            $table->decimal('power_w', 8, 1)->nullable();

            $table->foreign('product_id')->references('product_id')->on('products')->cascadeOnDelete();
        });

        Schema::create('storages', function (Blueprint $table) {
            $table->string('product_id')->primary();
            $table->integer('power_draw_w')->nullable();
            $table->string('interface_type')->nullable();
            $table->integer('pcie_version')->nullable();
            $table->integer('capacity_gb')->nullable();
            $table->decimal('power_w', 8, 1)->nullable();

            $table->foreign('product_id')->references('product_id')->on('products')->cascadeOnDelete();
        });

        Schema::create('psus', function (Blueprint $table) {
            $table->string('product_id')->primary();
            $table->decimal('power_w', 8, 1)->nullable();

            $table->foreign('product_id')->references('product_id')->on('products')->cascadeOnDelete();
        });

        Schema::create('coolers', function (Blueprint $table) {
            $table->string('product_id')->primary();
            $table->integer('cooler_height_mm')->nullable();
            $table->integer('connector_pin_count')->nullable();
            $table->integer('radiator_size_mm')->nullable();
            $table->decimal('power_w', 8, 1)->nullable();

            $table->foreign('product_id')->references('product_id')->on('products')->cascadeOnDelete();
        });

        Schema::create('cases', function (Blueprint $table) {
            $table->string('product_id')->primary();
            $table->json('form_factor')->nullable();
            $table->integer('max_gpu_length_mm')->nullable();
            $table->integer('max_cooler_height_mm')->nullable();
            $table->integer('m2_slots')->nullable();
            $table->integer('drive_bays')->nullable();
            $table->boolean('front_usb_c')->default(false);
            $table->boolean('audio_header')->default(true);
            $table->decimal('power_w', 8, 1)->nullable();

            $table->foreign('product_id')->references('product_id')->on('products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cases');
        Schema::dropIfExists('coolers');
        Schema::dropIfExists('psus');
        Schema::dropIfExists('storages');
        Schema::dropIfExists('gpus');
        Schema::dropIfExists('rams');
        Schema::dropIfExists('motherboards');
        Schema::dropIfExists('cpus');
    }
};
