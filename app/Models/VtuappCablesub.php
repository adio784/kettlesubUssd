<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VtuappCablesub extends Model
{
    use HasFactory;

    protected $table = 'vtuapp_cablesub';

    protected $fillable = [
        'smart_card_number',
        'balance_before',
        'balance_after',
        'plan_amount',
        'Status',
        'create_date',
        'ident',
        'refund',
        'customer_name',
        'cablename_id',
        'cableplan_id',
        'user_id',
    ];
}
