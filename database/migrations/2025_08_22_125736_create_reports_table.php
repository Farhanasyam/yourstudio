<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['daily_sales', 'monthly_sales', 'stock_report', 'low_stock', 'item_trends', 'cashier_performance', 'sales_by_category', 'profit_analysis']);
            $table->json('parameters')->nullable(); // date range, filters, etc
            $table->json('data')->nullable(); // cached report data
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('generated_at');
            $table->timestamps();
            
            $table->index(['type', 'generated_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reports');
    }
};
