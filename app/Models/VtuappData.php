<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VtuappData extends Model
{
    use HasFactory;

    protected $table = 'vtuapp_data';

    protected $fillable = [
        'data_type',
        'mobile_number',
        'Status',
        'medium',
        'create_date',
        'balance_before',
        'balance_after',
        'plan_amount',
        'Ported_number',
        'ident',
        'refund',
        'network_id',
        'plan_id',
        'user_id',
        'api_response',
    ];
}
