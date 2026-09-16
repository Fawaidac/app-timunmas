<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
Schema::create('order_items', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->unsignedBigInteger('order_id');
    $table->unsignedBigInteger('product_id');
    $table->integer('quantity');
    $table->decimal('price_per_unit', 15, 2);
    $table->decimal('subtotal', 15, 2);
    $table->timestamps();

    $table->foreign('order_id', 'fk_oi_so')->references('id')->on('sales_orders')->onDelete('cascade');
    $table->foreign('product_id', 'fk_oi_prod')->references('id')->on('products')->onDelete('cascade');
});
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};