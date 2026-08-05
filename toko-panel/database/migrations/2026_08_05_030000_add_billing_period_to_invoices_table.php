<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->date('billing_period_start')->nullable()->after('status');
            $table->date('billing_period_end')->nullable()->after('billing_period_start');
            $table->unique(['subscription_id', 'billing_period_start'], 'invoices_subscription_period_unique');
            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropIndex(['status', 'due_date']);
            $table->dropUnique('invoices_subscription_period_unique');
            $table->dropColumn(['billing_period_start', 'billing_period_end']);
        });
    }
};
