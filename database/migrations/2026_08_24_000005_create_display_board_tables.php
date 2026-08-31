<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('display_boards', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('device_key', 100)->nullable()->unique();
            $table->enum('type', ['board', 'score', 'queue', 'mixed'])->default('board');
            $table->string('location')->nullable();
            $table->boolean('is_online')->default(false)->index();
            $table->timestamp('last_seen_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('display_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('display_board_id')->constrained()->cascadeOnDelete();
            $table->string('content_type', 50);
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->string('media_path')->nullable();
            $table->unsignedInteger('duration_seconds')->default(10);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('display_contents');
        Schema::dropIfExists('display_boards');
    }
};
