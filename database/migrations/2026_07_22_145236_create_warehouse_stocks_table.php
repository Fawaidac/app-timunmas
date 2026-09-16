<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_stocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('warehouse_id');
            $table->bigInteger('product_id');
            $table->integer('stock_quantity');
            $table->timestamps();

            // Didefinisikan langsung di dalam Create Table
            $table->foreign('warehouse_id', 'fk_ws_wh')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreign('product_id', 'fk_ws_prod')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_stocks');
    }
};