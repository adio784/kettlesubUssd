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
        Schema::create('vtuapp_airtime', function (Blueprint $table) {
            $table->id();
            $table->string('pin', 30);
            $table->string('amount', 30);
            $table->double('Receivece_amount')->nullable();
            $table->string('Status', 30);
            $table->dateTime('create_date', 6);
            $table->string('ident', 30);
            $table->boolean('fund')->nullable();
            $table->timestamps();
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
        Schema::dropIfExists('vtuapp_airtimes');
    }
};
