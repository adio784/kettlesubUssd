<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VtuappTopuppercentage extends Model
{
    use HasFactory;

    protected $table = 'vtuapp_topuppercentage';

    protected $fillable = [
        'percent',
        'Affilliate_percent',
        'topuser_percent',
        'api_percent',
        'share_n_sell_percent',
        'share_n_sell_api_percent',
        'share_n_sell_affilliate_percent',
        'share_n_sell_topuser_percent',
        'network_id',
        'Bronze_percent',
        'Gold_percent',
        'Platinum_percent',
        'Silver_percent',
        'share_n_sell_Bronze_percent',
        'share_n_sell_Gold_percent',
        'share_n_sell_Platinum_percent',
        'share_n_sell_Silver_percent',
    ];
}
