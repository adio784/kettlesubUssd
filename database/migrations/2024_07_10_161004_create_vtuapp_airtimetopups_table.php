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
        Schema::create('vtuapp_airtimetopup', function (Blueprint $table) {
            $table->id();
            $table->string('mobile_number', 30);
            $table->string('airtime_type', 30);
            $table->string('amount', 30);
            $table->string('paid_amount', 30);
            $table->string('Status', 30);
            $table->dateTime('create_date', 6);
            $table->string('balance_before', 30);
            $table->string('balance_after', 30);
            $table->boolean('Ported_number')->nullable();
            $table->string('medium', 30);
            $table->string('ident', 300);
            $table->boolean('refund')->nullable();
            $table->integer('network_id');
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtuapp_airtimetopups');
    }
};
