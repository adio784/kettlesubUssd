<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VtuappAirtimetopup extends Model
{
    use HasFactory;

    protected $table = 'vtuapp_airtimetopup';

    protected $fillable = [
        'mobile_number',
        'airtime_type',
        'amount',
        'paid_amount',
        'Status',
        'create_date',
        'balance_before',
        'balance_after',
        'Ported_number',
        'medium',
        'ident',
        'refund',
        'network_id',
        'user_id',
    ];
}
