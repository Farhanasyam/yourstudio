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
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->string('item_name'); // Simpan nama item saat transaksi
            $table->string('item_sku'); // Simpan SKU item saat transaksi
            $table->string('barcode_scanned')->nullable(); // Barcode yang di-scan
            $table->decimal('unit_price', 12, 2); // Harga satuan saat transaksi
            $table->integer('quantity'); // Jumlah item
            $table->decimal('discount_per_item', 12, 2)->default(0); // Diskon per item
            $table->decimal('subtotal', 12, 2); // Subtotal per item (unit_price * quantity - discount)
            $table->timestamps();
            
            $table->index('transaction_id');
            $table->index('item_id');
            $table->index('barcode_scanned');
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
