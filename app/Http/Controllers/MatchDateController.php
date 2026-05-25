<?php

namespace App\Http\Controllers;

use App\Models\FootballMatch;

class MatchDateController extends Controller
{
    public function show(string $date)
    {
        $matches = FootballMatch::with(['entries.agent'])
            ->whereDate('match_date', $date)
            ->orderBy('title')
            ->get();

        return view('matches.date', compact('date', 'matches'));
    }
}
