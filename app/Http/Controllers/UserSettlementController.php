<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\Request;

class UserSettlementController extends Controller
{
    public function show(Request $request, Agent $agent)
    {
        $entries = $agent->entries()
            ->with('footballMatch')
            ->whereHas('footballMatch', function ($query) use ($request) {
                $query->when(! $request->boolean('date_all') && $request->filled('date'), fn ($q) => $q->whereDate('match_date', $request->date))
                    ->when(! $request->boolean('date_all') && ! $request->filled('date'), fn ($q) => $this->applyDateRange($q, $request))
                    ->when($request->filled('match'), fn ($q) => $q->where('title', 'like', '%' . $request->match . '%'));
            })
            ->get()
            ->sortByDesc(fn ($entry) => $entry->footballMatch->match_date);

        $totals = [
            'bet_amount' => $entries->sum('bet_amount'),
            'black_red' => $entries->sum('black_red_amount'),
            'my_winlose' => $entries->sum('my_winlose'),
            'rebate_amount' => $entries->sum('rebate_amount'),
            'run_ticket' => $entries->sum('run_ticket'),
            'net_total' => $entries->sum(fn ($entry) => $entry->net_total),
        ];

        return view('users.show', compact('agent', 'entries', 'totals'));
    }

    private function applyDateRange($query, Request $request)
    {
        if ($request->filled('date_from') && $request->filled('date_to')) {
            return $query->whereDate('match_date', '>=', $request->date_from)
                ->whereDate('match_date', '<=', $request->date_to);
        }

        if ($request->filled('date_from') || $request->filled('date_to')) {
            return $query->whereDate('match_date', $request->date_from ?: $request->date_to);
        }

        return $query;
    }
}
