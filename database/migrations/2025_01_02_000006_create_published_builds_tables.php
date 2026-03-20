<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('published_builds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name', 255);
            $table->text('description')->default('');
            $table->json('build_data'); // JSON with {slot: {product_id, name, price, main_image_path}}
            $table->integer('total_price')->default(0);
            $table->timestamps();
        });

        Schema::create('build_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('published_build_id')->constrained('published_builds')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('vote'); // +1 or -1
            $table->timestamps();
            $table->unique(['published_build_id', 'user_id']);
        });

        Schema::create('build_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('published_build_id')->constrained('published_builds')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('text');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('build_comments');
        Schema::dropIfExists('build_votes');
        Schema::dropIfExists('published_builds');
    }
};
