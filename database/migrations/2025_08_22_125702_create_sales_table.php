<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration
{
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code', 50)->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Kasir
            $table->date('transaction_date');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2);
            $table->decimal('change_amount', 15, 2)->default(0);
            $table->enum('payment_method', ['cash', 'debit', 'credit', 'transfer'])->default('cash');
            $table->text('notes')->nullable();
            $table->enum('status', ['completed', 'refunded', 'cancelled'])->default('completed');
            $table->timestamps();
            
            $table->index('transaction_code');
            $table->index('transaction_date');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales');
    }
};
