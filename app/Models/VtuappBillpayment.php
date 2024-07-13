<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VtuappBillpayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'paid_amount',
        'balance_before',
        'balance_after',
        'meter_number',
        'token',
        'Customer_Phone',
        'MeterType',
        'Status',
        'create_date',
        'ident',
        'refund',
        'customer_name',
        'customer_address',
        'disco_name_id',
        'user_id',
    ];
}
