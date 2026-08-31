<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservation_number', 40)->unique();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('court_id')->constrained()->restrictOnDelete();
            $table->date('booking_date')->index();
            $table->dateTime('start_at')->index();
            $table->dateTime('end_at')->index();
            $table->unsignedSmallInteger('duration_minutes');
            $table->unsignedTinyInteger('player_count')->default(1);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('service_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->enum('status', ['pending', 'confirmed', 'checked_in', 'completed', 'cancelled', 'no_show', 'expired'])->default('pending')->index();
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'refunded'])->default('unpaid')->index();
            $table->string('source', 30)->default('admin');
            $table->text('notes')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['court_id', 'start_at', 'end_at']);
            $table->index(['booking_date', 'status']);
        });

        Schema::create('reservation_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name')->nullable();
            $table->string('guest_phone', 30)->nullable();
            $table->enum('player_type', ['member', 'guest'])->default('member');
            $table->boolean('is_booking_contact')->default(false);
            $table->timestamps();
            $table->index(['reservation_id', 'member_id']);
        });

        Schema::create('reservation_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['reservation_id', 'created_at']);
        });

        Schema::create('reservation_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('item_type', 30)->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('total_price', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_addons');
        Schema::dropIfExists('reservation_status_logs');
        Schema::dropIfExists('reservation_players');
        Schema::dropIfExists('reservations');
    }
};
