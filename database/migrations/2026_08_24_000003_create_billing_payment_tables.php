<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source_type', 30)->default('reservation');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->dateTime('issued_at')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('service_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->decimal('refunded_amount', 14, 2)->default(0);
            $table->enum('status', ['draft', 'issued', 'partial', 'paid', 'overdue', 'cancelled', 'refunded'])->default('draft')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('item_type', 40);
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('description');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->timestamps();
            $table->index(['item_type', 'item_id']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 50)->unique();
            $table->foreignId('invoice_id')->constrained()->restrictOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->decimal('fee_amount', 14, 2)->default(0);
            $table->decimal('net_amount', 14, 2)->default(0);
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled', 'refunded'])->default('pending')->index();
            $table->string('reference')->nullable()->index();
            $table->dateTime('paid_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['invoice_id', 'status']);
        });

        Schema::create('payment_gateway_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('gateway', 50);
            $table->string('transaction_id')->nullable()->index();
            $table->string('order_id')->nullable()->index();
            $table->string('payment_type')->nullable();
            $table->string('transaction_status')->nullable();
            $table->string('fraud_status')->nullable();
            $table->decimal('gross_amount', 14, 2)->default(0);
            $table->decimal('fee_amount', 14, 2)->default(0);
            $table->json('payload')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->unique(['gateway', 'transaction_id']);
        });

        Schema::create('payment_callbacks', function (Blueprint $table) {
            $table->id();
            $table->string('gateway', 50);
            $table->string('event_type', 100)->nullable();
            $table->string('reference')->nullable()->index();
            $table->boolean('processed')->default(false)->index();
            $table->text('processing_error')->nullable();
            $table->json('payload');
            $table->timestamp('received_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->string('refund_number', 50)->unique();
            $table->foreignId('payment_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->enum('status', ['pending', 'processed', 'failed', 'cancelled'])->default('pending');
            $table->string('gateway_ref')->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payment_callbacks');
        Schema::dropIfExists('payment_gateway_transactions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
