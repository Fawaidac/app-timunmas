<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
Schema::create('invoices', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('invoice_number', 50)->unique();
    $table->unsignedBigInteger('order_id');
    $table->unsignedBigInteger('customer_id');
    $table->decimal('total_amount', 15, 2);
    $table->decimal('remaining_balance', 15, 2);
    $table->date('invoice_date');
    $table->date('due_date');
    $table->string('status', 20)->nullable();
    $table->timestamps();

    $table->foreign('order_id', 'fk_inv_so')->references('id')->on('sales_orders')->onDelete('cascade');
    $table->foreign('customer_id', 'fk_inv_cust')->references('id')->on('customers')->onDelete('cascade');
});
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};