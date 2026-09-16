<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
Schema::create('payments', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('payment_number', 50)->unique();
    $table->unsignedBigInteger('visit_id')->nullable();
    $table->unsignedBigInteger('invoice_id');
    $table->unsignedBigInteger('sales_id');
    $table->unsignedBigInteger('customer_id');
    $table->string('payment_method', 20)->nullable();
    $table->decimal('amount_paid', 15, 2);
    $table->string('reference_number', 100)->nullable();
    $table->string('proof_image_url')->nullable();
    $table->string('status', 20)->nullable();
    $table->text('rejection_reason')->nullable();
    $table->unsignedBigInteger('approved_by')->nullable();
    $table->timestamp('approved_at')->nullable();
    $table->timestamps();

    $table->foreign('visit_id', 'fk_pay_visit')->references('id')->on('sales_visits')->nullOnDelete();
    $table->foreign('invoice_id', 'fk_pay_inv')->references('id')->on('invoices')->onDelete('cascade');
    $table->foreign('sales_id', 'fk_pay_user')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('customer_id', 'fk_pay_cust')->references('id')->on('customers')->onDelete('cascade');
    $table->foreign('approved_by', 'fk_pay_appr')->references('id')->on('users')->nullOnDelete();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};