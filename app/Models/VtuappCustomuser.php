<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VtuappCustomuser extends Model
{
    use HasFactory;

    protected $fillable = [
        'password',
        'last_login',
        'is_superuser',
        'username',
        'first_name',
        'last_name',
        'is_staff',
        'is_active',
        'date_joined',
        'email',
        'FullName',
        'Address',
        'BankName',
        'AccountNumber',
        'Phone',
        'AccountName',
        'Account_Balance',
        'pin',
        'referer_username',
        'first_payment',
        'Referer_Bonus',
        'user_type',
        'reservedaccountNumber',
        'reservedbankName',
        'reservedaccountReference',
        'Bonus',
        'verify',
        'DOB',
        'Gender',
        'State_of_origin',
        'Local_gov_of_origin',
        'BVN',
        'passport_photogragh',
        'accounts',
        'traffic_source',
        'email_verify',
        'webhook',
        'fcm_token',
        'bonus_withdrawn',
    ];
}
