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
        Schema::create('vtuapp_cablesub', function (Blueprint $table) {
            $table->id();
            $table->string('smart_card_number', 30);
            $table->string('balance_before', 30);
            $table->string('balance_after', 30);
            $table->string('plan_amount', 30);
            $table->string('Status', 30);
            $table->dateTime('create_date', 6);
            $table->string('ident', 300);
            $table->boolean('refund')->nullable();
            $table->string('customer_name', 70)->nullable();
            $table->integer('cablename_id');
            $table->integer('cableplan_id');
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtuapp_cablesubs');
    }
};
