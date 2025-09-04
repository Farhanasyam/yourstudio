<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            // Add missing columns that are used in the controller
            $table->string('item_name')->nullable()->after('item_id');
            $table->string('item_sku')->nullable()->after('item_name');
            $table->string('barcode_scanned')->nullable()->after('item_sku');
            $table->decimal('discount_per_item', 15, 2)->default(0)->after('unit_price');
            
            // Keep discount_percent as is (no rename needed)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            // Remove added columns
            $table->dropColumn(['item_name', 'item_sku', 'barcode_scanned', 'discount_per_item']);
            
            // No rename needed
        });
    }
};
