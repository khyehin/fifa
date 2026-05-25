<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'football_match_id',
        'agent_id',
        'bet_amount',
        'ha',
        'ou',
        'black_red_amount',
        'my_percent',
        'my_winlose',
        'run_ticket',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'bet_amount' => 'decimal:2',
            'black_red_amount' => 'decimal:2',
            'my_percent' => 'decimal:4',
            'my_winlose' => 'decimal:2',
            'run_ticket' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $entry) {
            $entry->my_winlose = round((float) $entry->black_red_amount * (float) $entry->my_percent * -1, 2);
        });
    }

    public function footballMatch()
    {
        return $this->belongsTo(FootballMatch::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function getNetTotalAttribute(): float
    {
        return (float) $this->my_winlose + (float) $this->run_ticket;
    }

    public function getBetShareAttribute(): float
    {
        return round((float) $this->bet_amount * (float) $this->my_percent, 2);
    }
}
