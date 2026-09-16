<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
Schema::create('sales_orders', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('order_number', 50)->unique();
    $table->unsignedBigInteger('visit_id')->nullable();
    $table->unsignedBigInteger('customer_id');
    $table->unsignedBigInteger('sales_id');
    $table->date('order_date');
    $table->string('payment_type', 20)->nullable();
    $table->integer('payment_term_days');
    $table->decimal('total_amount', 15, 2);
    $table->string('status', 20)->nullable();
    $table->timestamps();

    $table->foreign('visit_id', 'fk_so_visit')->references('id')->on('sales_visits')->nullOnDelete();
    $table->foreign('customer_id', 'fk_so_cust')->references('id')->on('customers')->onDelete('cascade');
    $table->foreign('sales_id', 'fk_so_user')->references('id')->on('users')->onDelete('cascade');
});
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};