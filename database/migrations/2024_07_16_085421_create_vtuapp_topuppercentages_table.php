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
        Schema::create('vtuapp_topuppercentages', function (Blueprint $table) {
            $table->id();
            $table->integer('percent');
            $table->integer('Affilliate_percent');
            $table->integer('topuser_percent');
            $table->integer('api_percent');
            $table->integer('share_n_sell_percent');
            $table->integer('share_n_sell_api_percent');
            $table->integer('share_n_sell_affilliate_percent');
            $table->integer('share_n_sell_topuser_percent');
            $table->integer('network_id');
            $table->integer('Bronze_percent');
            $table->integer('Gold_percent');
            $table->integer('Platinum_percent');
            $table->integer('Silver_percent');
            $table->integer('share_n_sell_Bronze_percent');
            $table->integer('share_n_sell_Gold_percent');
            $table->integer('share_n_sell_Platinum_percent');
            $table->integer('share_n_sell_Silver_percent');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtuapp_topuppercentages');
    }
};
