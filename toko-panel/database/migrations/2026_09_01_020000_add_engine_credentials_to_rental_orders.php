<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_orders', function (Blueprint $table) {
            $table->string('engine_login_email')->nullable();
            $table->text('engine_temporary_password')->nullable();
            $table->timestamp('credentials_viewed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('rental_orders', function (Blueprint $table) {
            $table->dropColumn(['engine_login_email', 'engine_temporary_password', 'credentials_viewed_at']);
        });
    }
};
