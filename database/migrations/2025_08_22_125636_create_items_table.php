<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sku', 100)->unique(); // SKU untuk barcode
            $table->string('barcode', 255)->unique()->nullable(); // Generated barcode
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->string('unit', 50)->default('pcs'); // pcs, kg, liter, etc
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('sku');
            $table->index('barcode');
        });
    }

    public function down()
    {
        Schema::dropIfExists('items');
    }
};
