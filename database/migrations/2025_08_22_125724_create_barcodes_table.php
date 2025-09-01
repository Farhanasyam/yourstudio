<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBarcodesTable extends Migration
{
    public function up()
    {
        Schema::create('barcodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->string('barcode_type', 50)->default('CODE128'); // CODE128, EAN13, etc
            $table->string('barcode_value', 255);
            $table->string('barcode_image_path')->nullable();
            $table->boolean('is_printed')->default(false);
            $table->timestamp('printed_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['item_id', 'barcode_value']);
            $table->index('barcode_value');
        });
    }

    public function down()
    {
        Schema::dropIfExists('barcodes');
    }
};
