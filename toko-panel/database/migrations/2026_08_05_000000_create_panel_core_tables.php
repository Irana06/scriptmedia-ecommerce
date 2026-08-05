<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->enum('name', ['starter', 'standard', 'pro'])->unique();
            $table->string('slug')->unique();
            $table->decimal('price_platform', 15, 2);
            $table->decimal('price_care_monthly', 15, 2);
            $table->decimal('price_care_annual', 15, 2);
            $table->unsignedInteger('max_products')->nullable();
            $table->unsignedSmallInteger('max_payment_gateways')->nullable();
            $table->unsignedSmallInteger('content_request_quota');
            $table->unsignedSmallInteger('support_sla_hours');
            $table->boolean('custom_domain_allowed')->default(false);
            $table->boolean('allow_realtime_shipping')->default(false);
            $table->boolean('allow_full_design_customization')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subdomain')->unique();
            $table->string('custom_domain')->nullable()->unique();
            $table->foreignId('owner_user_id')->constrained('users')->restrictOnDelete();
            $table->string('database_name')->unique();
            $table->enum('provisioning_status', ['pending', 'provisioning', 'active', 'failed'])->default('pending');
            $table->enum('store_status', ['active', 'grace_period', 'suspended', 'cancelled'])->default('active');
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            $table->enum('billing_cycle', ['monthly', 'annual']);
            $table->enum('status', ['active', 'grace_period', 'suspended', 'cancelled'])->default('active');
            $table->date('current_period_start');
            $table->date('current_period_end');
            $table->date('next_billing_date');
            $table->foreignId('pending_plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->enum('status', ['unpaid', 'paid', 'overdue', 'cancelled'])->default('unpaid');
            $table->decimal('subtotal_platform', 15, 2);
            $table->decimal('subtotal_care', 15, 2);
            $table->decimal('total', 15, 2);
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->enum('gateway', ['midtrans', 'xendit', 'manual']);
            $table->string('gateway_reference')->nullable()->unique();
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('addons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 15, 2);
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('tenant_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('addon_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['active', 'cancelled'])->default('active');
            $table->timestamp('activated_at');
            $table->timestamps();

            $table->unique(['tenant_id', 'addon_id']);
        });

        Schema::create('content_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by_user_id')->constrained('users')->restrictOnDelete();
            $table->text('description');
            $table->enum('status', ['pending', 'in_progress', 'done', 'rejected'])->default('pending');
            $table->date('usage_period_start');
            $table->timestamps();

            $table->index(['tenant_id', 'usage_period_start']);
        });

        Schema::create('plan_feature_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->unsignedInteger('products_count')->default(0);
            $table->unsignedSmallInteger('content_requests_used')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_feature_usages');
        Schema::dropIfExists('content_change_requests');
        Schema::dropIfExists('tenant_addons');
        Schema::dropIfExists('addons');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('tenants');
        Schema::dropIfExists('plans');
    }
};
