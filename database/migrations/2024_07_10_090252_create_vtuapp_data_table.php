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
        Schema::create('vtuapp_data', function (Blueprint $table) {
            $table->id();
            $table->string('data_type', 30)->nullable();
            $table->string('mobile_number', 30);
            $table->string('Status', 30);
            $table->string('medium', 30);
            $table->dateTime('create_date', 6);
            $table->string('balance_before', 30);
            $table->string('balance_after', 30)->nullable();
            $table->string('plan_amount', 30);
            $table->boolean('Ported_number')->nullable();
            $table->string('ident', 30);
            $table->boolean('refund')->nullable();
            $table->longText('api_response');
            $table->integer('network_id');
            $table->integer('plan_id');
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtuapp_data');
    }
};
