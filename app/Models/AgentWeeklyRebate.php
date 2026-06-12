<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentWeeklyRebate extends Model
{
    protected $fillable = [
        'agent_id',
        'week_start',
        'rebate_percent',
    ];

    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'rebate_percent' => 'decimal:4',
        ];
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}
