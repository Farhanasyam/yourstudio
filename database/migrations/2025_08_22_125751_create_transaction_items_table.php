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
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade'); // ID transaksi
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade'); // ID item
            $table->integer('quantity'); // Jumlah item
            $table->decimal('unit_price', 15, 2); // Harga satuan
            $table->decimal('discount_percent', 5, 2)->default(0); // Persentase diskon
            $table->decimal('discount_amount', 15, 2)->default(0); // Jumlah diskon
            $table->decimal('subtotal', 15, 2); // Subtotal untuk item ini
            $table->timestamps();
            
            $table->index('transaction_id');
            $table->index('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
