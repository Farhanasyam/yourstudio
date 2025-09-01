<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockAdjustmentsTable extends Migration
{
    public function up()
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code', 50)->unique();
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['increase', 'decrease']);
            $table->integer('quantity');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->enum('reason', ['damaged', 'expired', 'lost', 'found', 'correction', 'other']);
            $table->text('notes')->nullable();
            $table->date('adjustment_date');
            $table->timestamps();
            
            $table->index('transaction_code');
            $table->index('adjustment_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
