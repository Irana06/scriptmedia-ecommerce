<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('billing_cycle', ['monthly', 'annual']);
            $table->string('business_name');
            $table->string('desired_subdomain')->unique();
            $table->string('custom_domain')->nullable()->unique();
            $table->string('whatsapp', 30);
            $table->text('notes')->nullable();
            $table->enum('status', ['awaiting_payment', 'paid', 'provisioning', 'ready', 'cancelled'])->default('awaiting_payment');
            $table->decimal('amount', 15, 2);
            $table->string('payment_gateway')->default('midtrans');
            $table->string('payment_reference')->nullable()->unique();
            $table->text('payment_checkout_token')->nullable();
            $table->text('payment_checkout_url')->nullable();
            $table->json('payment_metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_orders');
    }
};
