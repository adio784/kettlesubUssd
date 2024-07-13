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
        Schema::create('vtuapp_networks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30)->unique();
            $table->string('status', 30);
            $table->string('data_vending_medium', 30);
            $table->string('corporate_data_vending_medium', 30);
            $table->string('vtu_vending_medium', 30)->nullable();
            $table->string('msorg_web_net_id', 5)->nullable();
            $table->string('share_and_sell_vending_medium', 30)->nullable();
            $table->integer('convertion_parcentage')->nullable();
            $table->boolean('sme_disable')->nullable();
            $table->boolean('gifting_disable')->nullable();
            $table->boolean('airtime_disable')->nullable();
            $table->boolean('data_disable')->nullable();
            $table->boolean('recharge_pin_disable')->nullable();
            $table->boolean('share_and_sell_disable')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtuapp_networks');
    }
};
