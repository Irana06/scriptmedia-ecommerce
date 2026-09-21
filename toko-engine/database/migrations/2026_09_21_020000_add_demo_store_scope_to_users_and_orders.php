<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            /*
             * Binds an admin account to one demo storefront so the team can see the
             * store administration of a single plan. Null keeps the account on the
             * whole catalogue, which is how a real single-tenant install behaves.
             */
            $table->string('demo_store')->nullable()->after('email');
        });

        Schema::table('orders', function (Blueprint $table): void {
            // Which storefront the order was placed from, so the admin can scope to it.
            $table->string('demo_store')->nullable()->after('number')->index();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['demo_store']);
            $table->dropColumn('demo_store');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('demo_store');
        });
    }
};
