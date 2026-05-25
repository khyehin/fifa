<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'default_bet_amount',
        'my_percent',
        'run_ticket',
        'remarks',
        'is_active',
        'bet_amount_locked',
    ];

    protected function casts(): array
    {
        return [
            'default_bet_amount' => 'decimal:2',
            'my_percent' => 'decimal:4',
            'run_ticket' => 'decimal:2',
            'is_active' => 'boolean',
            'bet_amount_locked' => 'boolean',
        ];
    }

    public function entries()
    {
        return $this->hasMany(MatchEntry::class);
    }
}
