<?php

namespace App\Http\Controllers;

use App\Models\FootballMatch;
use App\Models\Agent;
use Illuminate\Http\Request;

class FootballMatchController extends Controller
{
    public function index(Request $request)
    {
        $dateOrder = $request->date_order === 'asc' ? 'asc' : 'desc';

        $matches = FootballMatch::withCount('entries')
            ->when(! $request->boolean('date_all'), fn ($q) => $this->applyDateRange($q, $request))
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where(function ($search) use ($request) {
                    $search->where('title', 'like', '%' . $request->q . '%')
                        ->orWhere('home_team', 'like', '%' . $request->q . '%')
                        ->orWhere('away_team', 'like', '%' . $request->q . '%');
                });
            })
            ->orderBy('match_date', $dateOrder)
            ->orderBy('title')
            ->paginate(30)
            ->withQueryString();

        return view('matches.index', compact('matches'));
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

    public function create()
    {
        return view('matches.form', ['match' => new FootballMatch()]);
    }

    public function store(Request $request)
    {
        $match = FootballMatch::create($this->validated($request));

        return redirect()->route('matches.show', $match)->with('status', 'Match created.');
    }

    public function show(FootballMatch $match)
    {
        $match->load(['entries.agent']);
        $agents = Agent::where('is_active', true)->orderBy('username')->get();

        return view('matches.show', compact('match', 'agents'));
    }

    public function edit(FootballMatch $match)
    {
        return view('matches.form', compact('match'));
    }

    public function update(Request $request, FootballMatch $match)
    {
        $match->update($this->validated($request));

        return redirect()->route('matches.show', $match)->with('status', 'Match updated.');
    }

    public function destroy(FootballMatch $match)
    {
        $match->delete();

        return redirect()->route('matches.index')->with('status', 'Match deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'match_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'home_team' => ['required', 'string', 'max:255'],
            'away_team' => ['required', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);
    }
}
