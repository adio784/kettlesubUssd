<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vtuapp_billpayments', function (Blueprint $table) {
            $table->id();
            $table->string('amount', 30);
            $table->string('paid_amount', 30);
            $table->string('balance_before', 30);
            $table->string('balance_after', 30);
            $table->string('meter_number', 30);
            $table->string('token', 200)->nullable();
            $table->string('Customer_Phone', 15)->nullable();
            $table->string('MeterType', 30)->nullable();
            $table->string('Status', 30);
            $table->dateTime('create_date', 6);
            $table->string('ident', 300);
            $table->boolean('refund')->nullable();
            $table->string('customer_name', 250)->nullable();
            $table->string('customer_address', 500)->nullable();
            $table->integer('disco_name_id');
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtuapp_billpayments');
    }
};
