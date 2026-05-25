<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FootballMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_date',
        'title',
        'home_team',
        'away_team',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'match_date' => 'date',
        ];
    }

    public function entries()
    {
        return $this->hasMany(MatchEntry::class);
    }
}
