<?php

namespace App\Http\Controllers;

use App\Models\FootballMatch;
use App\Models\MatchEntry;
use App\Models\Agent;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $request = $this->withDefaultAll($request);
        $entries = $this->filteredEntries($request)->get();

        $totals = [
            'bet_amount' => $entries->sum('bet_amount'),
            'black_red' => $entries->sum('black_red_amount'),
            'my_winlose' => $entries->sum('my_winlose'),
            'run_ticket' => $entries->sum('run_ticket'),
            'net_total' => $entries->sum(fn ($entry) => $entry->net_total),
        ];

        $matchByDate = FootballMatch::with('entries')
            ->when(! $request->boolean('date_all') && $request->filled('date'), fn ($q) => $q->whereDate('match_date', $request->date))
            ->when(! $request->boolean('date_all') && $request->filled('date_from'), fn ($q) => $q->whereDate('match_date', '>=', $request->date_from))
            ->when(! $request->boolean('date_all') && $request->filled('date_to'), fn ($q) => $q->whereDate('match_date', '<=', $request->date_to))
            ->when($request->filled('match'), fn ($q) => $q->where('title', 'like', '%' . $request->match . '%'))
            ->when($request->filled('team'), function ($q) use ($request) {
                $q->where(function ($teamQuery) use ($request) {
                    $teamQuery->where('home_team', 'like', '%' . $request->team . '%')
                        ->orWhere('away_team', 'like', '%' . $request->team . '%');
                });
            })
            ->orderByDesc('match_date')
            ->orderBy('title')
            ->get()
            ->groupBy(fn ($match) => $match->match_date->format('Y-m-d'))
            ->sortKeysDesc();

        $agents = Agent::query()
            ->when($request->filled('username'), fn ($q) => $q->where('username', 'like', '%' . $request->username . '%'))
            ->orderBy('username')
            ->get();

        $weeklySheets = $this->weeklySheets($request, $entries, $agents);

        return view('dashboard', compact('entries', 'matchByDate', 'weeklySheets', 'totals'));
    }

    private function filteredEntries(Request $request)
    {
        return MatchEntry::with(['agent', 'footballMatch'])
            ->whereHas('footballMatch', function ($query) use ($request) {
                $query->when(! $request->boolean('date_all') && $request->filled('date'), fn ($q) => $q->whereDate('match_date', $request->date))
                    ->when(! $request->boolean('date_all') && $request->filled('date_from'), fn ($q) => $q->whereDate('match_date', '>=', $request->date_from))
                    ->when(! $request->boolean('date_all') && $request->filled('date_to'), fn ($q) => $q->whereDate('match_date', '<=', $request->date_to))
                    ->when($request->filled('match'), fn ($q) => $q->where('title', 'like', '%' . $request->match . '%'))
                    ->when($request->filled('team'), function ($q) use ($request) {
                        $q->where(function ($teamQuery) use ($request) {
                            $teamQuery->where('home_team', 'like', '%' . $request->team . '%')
                                ->orWhere('away_team', 'like', '%' . $request->team . '%');
                        });
                    });
            })
            ->whereHas('agent', fn ($query) => $query->when($request->filled('username'), fn ($q) => $q->where('username', 'like', '%' . $request->username . '%')))
            ->latest('updated_at');
    }

    private function withDefaultAll(Request $request): Request
    {
        if (! $request->boolean('date_all') && ! $request->filled('date') && ! $request->filled('date_from') && ! $request->filled('date_to')) {
            $request->merge([
                'date_all' => '1',
            ]);
        }

        return $request;
    }

    private function dateColumns(Request $request, $entries): array
    {
        if ($request->filled('date')) {
            return [Carbon::parse($request->date)];
        }

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $start = Carbon::parse($request->date_from ?: $request->date_to);
            $end = Carbon::parse($request->date_to ?: $request->date_from);

            return collect(CarbonPeriod::create($start, $end))->sortDesc()->values()->all();
        }

        $dates = $entries
            ->map(fn ($entry) => $entry->footballMatch->match_date->copy()->startOfDay())
            ->unique(fn (Carbon $date) => $date->format('Y-m-d'))
            ->sortDesc()
            ->values();

        return $dates->isNotEmpty()
            ? $dates->all()
            : [now()->startOfDay()];
    }

    private function dateRangeLabel(array $dateColumns): string
    {
        $first = collect($dateColumns)->sort()->first();
        $last = collect($dateColumns)->sort()->last();

        if (! $first || ! $last) {
            return now()->format('d/m');
        }

        if ($first->isSameDay($last)) {
            return $first->format('d/m');
        }

        return $first->format('d') . '-' . $last->format('d/m');
    }

    private function weeklySheets(Request $request, $entries, $agents)
    {
        $weeks = $this->weekColumns($request, $entries);

        return collect($weeks)->map(function (array $dateColumns) use ($entries, $agents) {
            $weekStart = collect($dateColumns)->sort()->first();
            $weekEnd = collect($dateColumns)->sort()->last();

            $weekEntries = $entries->filter(function ($entry) use ($weekStart, $weekEnd) {
                $date = $entry->footballMatch->match_date;

                return $date->betweenIncluded($weekStart, $weekEnd);
            });

            $rows = $agents->map(function (Agent $agent) use ($weekEntries, $dateColumns) {
                $agentEntries = $weekEntries->where('agent_id', $agent->id);
                if ($agentEntries->isEmpty()) {
                    return null;
                }

                $firstEntry = $agentEntries->sortBy(fn ($entry) => $entry->footballMatch->match_date)->first();
                $dailyAmounts = collect($dateColumns)->mapWithKeys(function (Carbon $date) use ($agentEntries) {
                    $dateKey = $date->format('Y-m-d');
                    $amount = $agentEntries
                        ->filter(fn ($entry) => $entry->footballMatch->match_date->format('Y-m-d') === $dateKey)
                        ->sum('black_red_amount');

                    return [$dateKey => $amount];
                });

                return [
                    'agent' => $agent,
                    'bet_amount' => $firstEntry->bet_amount,
                    'daily_amounts' => $dailyAmounts,
                    'black_red' => $agentEntries->sum('black_red_amount'),
                    'my_percent' => $firstEntry->my_percent,
                    'my_winlose' => $agentEntries->sum('my_winlose'),
                    'run_ticket' => $agentEntries->sum('run_ticket'),
                    'net_total' => $agentEntries->sum(fn ($entry) => $entry->net_total),
                ];
            })->filter()->values();

            return [
                'label' => $this->dateRangeLabel($dateColumns),
                'date_columns' => $dateColumns,
                'rows' => $rows,
                'totals' => [
                    'black_red' => $weekEntries->sum('black_red_amount'),
                    'my_winlose' => $weekEntries->sum('my_winlose'),
                    'run_ticket' => $weekEntries->sum('run_ticket'),
                    'net_total' => $weekEntries->sum(fn ($entry) => $entry->net_total),
                ],
            ];
        });
    }

    private function weekColumns(Request $request, $entries): array
    {
        if (! $request->boolean('date_all') && ($request->filled('date') || $request->filled('date_from') || $request->filled('date_to'))) {
            $dates = collect($this->dateColumns($request, $entries));
        } else {
            $dates = $entries
                ->map(fn ($entry) => $entry->footballMatch->match_date->copy()->startOfDay())
                ->unique(fn (Carbon $date) => $date->format('Y-m-d'))
                ->values();
        }

        if ($dates->isEmpty()) {
            $dates = collect([now()->startOfDay()]);
        }

        return $dates
            ->groupBy(fn (Carbon $date) => $date->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'))
            ->map(function ($weekDates, string $weekStart) {
                $start = Carbon::parse($weekStart);

                return collect(CarbonPeriod::create($start, $start->copy()->endOfWeek(Carbon::SUNDAY)))
                    ->values()
                    ->all();
            })
            ->sortKeysDesc()
            ->values()
            ->all();
    }
}
