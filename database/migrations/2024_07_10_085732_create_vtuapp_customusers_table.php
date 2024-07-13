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
        Schema::create('vtuapp_customuser', function (Blueprint $table) {
            $table->id();
            $table->string('password', 128);
            $table->dateTime('last_login', 6)->nullable();
            $table->boolean('is_superuser');
            $table->string('username', 150)->unique();
            $table->string('first_name', 150);
            $table->string('last_name', 150);
            $table->boolean('is_staff');
            $table->boolean('is_active');
            $table->dateTime('date_joined', 6);
            $table->string('email', 254);
            $table->string('FullName', 200)->nullable();
            $table->string('Address', 500)->nullable();
            $table->string('BankName', 100);
            $table->string('AccountNumber', 40);
            $table->string('Phone', 30);
            $table->string('AccountName', 200);
            $table->double('Account_Balance')->nullable();
            $table->string('pin', 5)->nullable();
            $table->string('referer_username', 50)->nullable();
            $table->boolean('first_payment');
            $table->double('Referer_Bonus')->nullable();
            $table->string('user_type', 30)->nullable();
            $table->string('reservedaccountNumber', 100)->nullable();
            $table->string('reservedbankName', 100)->nullable();
            $table->string('reservedaccountReference', 100)->nullable();
            $table->double('Bonus')->nullable();
            $table->boolean('verify');
            $table->date('DOB')->nullable();
            $table->string('Gender', 6)->nullable();
            $table->string('State_of_origin', 100)->nullable();
            $table->string('Local_gov_of_origin', 100)->nullable();
            $table->string('BVN', 50)->nullable();
            $table->string('passport_photogragh', 100)->nullable();
            $table->longText('accounts');
            $table->string('traffic_source', 30)->nullable();
            $table->boolean('email_verify');
            $table->string('webhook', 200)->nullable();
            $table->string('fcm_token', 255)->nullable();
            $table->integer('bonus_withdrawn')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtuapp_customusers');
    }
};
