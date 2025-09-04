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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->unique(); // Kode transaksi unik
            $table->foreignId('cashier_id')->constrained('users')->onDelete('cascade'); // ID kasir
            $table->dateTime('transaction_date'); // Tanggal transaksi
            $table->decimal('subtotal', 15, 2)->default(0); // Subtotal sebelum diskon/pajak
            $table->decimal('discount_amount', 15, 2)->default(0); // Jumlah diskon
            $table->decimal('tax_amount', 15, 2)->default(0); // Jumlah pajak
            $table->decimal('total_amount', 15, 2); // Total akhir
            $table->decimal('paid_amount', 15, 2); // Jumlah yang dibayar
            $table->decimal('change_amount', 15, 2)->default(0); // Kembalian
            $table->enum('payment_method', ['cash', 'card', 'transfer', 'qris'])->default('cash'); // Metode pembayaran
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending'); // Status transaksi
            $table->text('notes')->nullable(); // Catatan tambahan
            $table->timestamps();
            
            $table->index('transaction_code');
            $table->index('cashier_id');
            $table->index('transaction_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
