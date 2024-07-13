<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VtuappNetwork extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'data_vending_medium',
        'corporate_data_vending_medium',
        'vtu_vending_medium',
        'msorg_web_net_id',
        'share_and_sell_vending_medium',
        'convertion_parcentage',
        'sme_disable',
        'gifting_disable',
        'airtime_disable',
        'data_disable',
        'recharge_pin_disable',
        'share_and_sell_disable',
    ];
}
