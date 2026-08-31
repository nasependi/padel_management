<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('member_code', 30)->unique();
            $table->string('name');
            $table->string('phone', 30)->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('photo_path')->nullable();
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active')->index();
            $table->timestamp('joined_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('courts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('location')->nullable();
            $table->enum('court_type', ['indoor', 'outdoor', 'covered'])->default('indoor');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('court_operating_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0 Sunday - 6 Saturday
            $table->time('open_time');
            $table->time('close_time');
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
            $table->unique(['court_id', 'day_of_week']);
        });

        Schema::create('price_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('court_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('day_of_week')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->decimal('price', 14, 2);
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('priority')->default(0);
            $table->timestamps();
            $table->index(['court_id', 'day_of_week', 'start_time', 'end_time']);
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->enum('type', ['cash', 'bank_transfer', 'qris', 'card', 'e_wallet', 'gateway', 'other']);
            $table->string('provider')->nullable();
            $table->boolean('is_online')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('price_rules');
        Schema::dropIfExists('court_operating_hours');
        Schema::dropIfExists('courts');
        Schema::dropIfExists('members');
    }
};
