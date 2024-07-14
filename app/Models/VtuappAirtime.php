<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VtuappAirtime extends Model
{
    use HasFactory;

    protected $table = 'vtuapp_airtime';

    protected $fillable = [
        'pin',
        'amount',
        'Receivece_amount',
        'Status',
        'create_date',
        'ident',
        'fund',
        'network_id',
        'user_id',
    ];
}
