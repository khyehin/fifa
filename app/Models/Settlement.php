<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settlement extends Model
{
    protected $fillable = [
        'agent_id',
        'settlement_date',
        'total_bet_amount',
        'total_black_red',
        'total_my_winlose',
        'total_run_ticket',
        'net_total',
        'remarks',
    ];
}
