<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            // Label shown to shoppers, e.g. "Merah / L".
            $table->string('name');
            $table->string('sku')->nullable();
            /*
             * The attribute pairs this variant stands for, e.g. {"Warna":"Merah","Ukuran":"L"}.
             * Option groups and their values are derived from these, so adding a new
             * attribute needs no schema change.
             */
            $table->json('options');
            $table->decimal('price', 14, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->index(['product_id', 'is_active']);
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
            // Kept as text so the order still reads correctly if the variant is renamed or removed.
            $table->string('variant_name')->nullable()->after('product_name');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('product_variant_id');
            $table->dropColumn('variant_name');
        });

        Schema::dropIfExists('product_variants');
    }
};
