<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
Schema::create('sales_visits', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->unsignedBigInteger('sales_id');
    $table->unsignedBigInteger('customer_id');
    $table->date('visit_date');
    $table->string('purpose', 100)->nullable();
    $table->string('status', 20)->nullable();
    $table->timestamp('checkin_time')->nullable();
    $table->decimal('checkin_latitude', 10, 8)->nullable();
    $table->decimal('checkin_longitude', 11, 8)->nullable();
    $table->integer('distance_meters')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();

    $table->foreign('sales_id', 'fk_sv_user')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('customer_id', 'fk_sv_cust')->references('id')->on('customers')->onDelete('cascade');
});
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_visits');
    }
};