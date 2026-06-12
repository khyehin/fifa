<?php

namespace App\Http\Controllers;

use App\Models\FootballMatch;
use App\Models\MatchEntry;
use App\Models\Agent;
use App\Models\AgentWeeklyRebate;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $hasDateFilter = $this->hasDateFilter($request);
        $request = $this->withDefaultAll($request);
        $entries = $this->filteredEntries($request)->get();
        $systemEntries = MatchEntry::with('footballMatch')->get();
        $weeklyRebates = $this->weeklyRebateSettings();
        $systemRebate = $this->weeklyRebateTotal($systemEntries, $weeklyRebates);
        $currentWeekStart = now()->startOfWeek(Carbon::MONDAY);
        $currentWeekEnd = now()->endOfWeek(Carbon::SUNDAY);

        $systemTotals = [
            'bet_amount' => $systemEntries->sum('bet_amount'),
            'black_red' => $systemEntries->sum('black_red_amount'),
            'my_winlose' => $systemEntries->sum('my_winlose'),
            'rebate_amount' => $systemRebate,
            'run_ticket' => $systemEntries->sum('run_ticket'),
            'net_total' => $systemEntries->sum(fn ($entry) => $entry->net_total) - $systemRebate,
        ];

        $matchByDate = FootballMatch::with('entries')
            ->when(! $hasDateFilter, fn ($q) => $q->whereDate('match_date', '>=', $currentWeekStart)->whereDate('match_date', '<=', $currentWeekEnd))
            ->when($hasDateFilter && ! $request->boolean('date_all') && $request->filled('date'), fn ($q) => $q->whereDate('match_date', $request->date))
            ->when($hasDateFilter && ! $request->boolean('date_all') && ! $request->filled('date'), fn ($q) => $this->applyDateRange($q, $request))
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
            ->sortKeys();

        $agents = Agent::query()
            ->when($request->filled('username'), fn ($q) => $q->where('username', 'like', '%' . $request->username . '%'))
            ->orderBy('username')
            ->get();

        $weeklySheets = $this->weeklySheets($request, $entries, $agents, $weeklyRebates);

        return view('dashboard', compact('entries', 'matchByDate', 'weeklySheets', 'systemTotals'));
    }

    public function updateWeeklyRebate(Request $request)
    {
        $data = $request->validate([
            'agent_id' => ['required', 'exists:agents,id'],
            'week_start' => ['required', 'date'],
            'rebate_percent' => ['nullable', 'numeric'],
        ]);

        $rebate = AgentWeeklyRebate::updateOrCreate(
            [
                'agent_id' => $data['agent_id'],
                'week_start' => Carbon::parse($data['week_start'])->startOfWeek(Carbon::MONDAY)->toDateString(),
            ],
            [
                'rebate_percent' => $data['rebate_percent'] ?? 0,
            ]
        );

        return response()->json([
            'saved' => true,
            'rebate_percent' => number_format((float) $rebate->rebate_percent, 4, '.', ''),
        ]);
    }

    private function filteredEntries(Request $request)
    {
        return MatchEntry::with(['agent', 'footballMatch'])
            ->whereHas('footballMatch', function ($query) use ($request) {
                $query->when(! $request->boolean('date_all') && $request->filled('date'), fn ($q) => $q->whereDate('match_date', $request->date))
                    ->when(! $request->boolean('date_all') && ! $request->filled('date'), fn ($q) => $this->applyDateRange($q, $request))
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

    private function hasDateFilter(Request $request): bool
    {
        return $request->boolean('date_all')
            || $request->filled('date')
            || $request->filled('date_from')
            || $request->filled('date_to');
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

    private function dateColumns(Request $request, $entries): array
    {
        if ($request->filled('date')) {
            return [Carbon::parse($request->date)];
        }

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $start = Carbon::parse($request->date_from ?: $request->date_to);
            $end = Carbon::parse($request->date_to ?: $request->date_from);

            return collect(CarbonPeriod::create($start, $end))->values()->all();
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

    private function weeklySheets(Request $request, $entries, $agents, $weeklyRebates)
    {
        $weeks = $this->weekColumns($request, $entries);

        return collect($weeks)->map(function (array $dateColumns) use ($entries, $agents, $weeklyRebates) {
            $weekStart = collect($dateColumns)->sort()->first();
            $weekEnd = collect($dateColumns)->sort()->last();

            $weekEntries = $entries->filter(function ($entry) use ($weekStart, $weekEnd) {
                $date = $entry->footballMatch->match_date;

                return $date->betweenIncluded($weekStart, $weekEnd);
            });

            $rows = $agents->map(function (Agent $agent) use ($weekEntries, $dateColumns, $weekStart, $weeklyRebates) {
                $agentEntries = $weekEntries->where('agent_id', $agent->id);
                if ($agentEntries->isEmpty()) {
                    return null;
                }

                $firstEntry = $agentEntries->sortBy(fn ($entry) => $entry->footballMatch->match_date)->first();
                $blackRedTotal = $agentEntries->sum('black_red_amount');
                $rebatePercent = $this->weeklyRebatePercent($weeklyRebates, $agent->id, $weekStart);
                $rebateAmount = $this->weeklyRebateAmount($blackRedTotal, $rebatePercent);
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
                    'black_red' => $blackRedTotal,
                    'my_percent' => $firstEntry->my_percent,
                    'rebate_percent' => $rebatePercent,
                    'my_winlose' => $agentEntries->sum('my_winlose'),
                    'rebate_amount' => $rebateAmount,
                    'run_ticket' => $agentEntries->sum('run_ticket'),
                    'net_total' => $agentEntries->sum(fn ($entry) => $entry->net_total) - $rebateAmount,
                ];
            })->filter()->values();

            return [
                'label' => $this->dateRangeLabel($dateColumns),
                'week_start' => $weekStart->format('Y-m-d'),
                'date_columns' => $dateColumns,
                'rows' => $rows,
                'totals' => [
                    'black_red' => $weekEntries->sum('black_red_amount'),
                    'my_winlose' => $weekEntries->sum('my_winlose'),
                    'rebate_amount' => $rows->sum('rebate_amount'),
                    'run_ticket' => $weekEntries->sum('run_ticket'),
                    'net_total' => $rows->sum('net_total'),
                ],
            ];
        });
    }

    private function weeklyRebateTotal($entries, $weeklyRebates): float
    {
        return (float) $entries
            ->groupBy(fn ($entry) => $this->weeklyRebateKey($entry->agent_id, $entry->footballMatch->match_date))
            ->sum(function ($agentWeekEntries) use ($weeklyRebates) {
                $firstEntry = $agentWeekEntries->first();
                $weekStart = $firstEntry->footballMatch->match_date->copy()->startOfWeek(Carbon::MONDAY);

                return $this->weeklyRebateAmount(
                    (float) $agentWeekEntries->sum('black_red_amount'),
                    $this->weeklyRebatePercent($weeklyRebates, $firstEntry->agent_id, $weekStart)
                );
            });
    }

    private function weeklyRebateSettings()
    {
        return AgentWeeklyRebate::query()
            ->get()
            ->keyBy(fn (AgentWeeklyRebate $rebate) => $this->weeklyRebateKey($rebate->agent_id, $rebate->week_start));
    }

    private function weeklyRebatePercent($weeklyRebates, int $agentId, Carbon $weekStart): float
    {
        return (float) optional($weeklyRebates->get($this->weeklyRebateKey($agentId, $weekStart)))->rebate_percent;
    }

    private function weeklyRebateKey(int $agentId, Carbon|string $weekStart): string
    {
        return $agentId . '|' . Carbon::parse($weekStart)->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
    }

    private function weeklyRebateAmount(float $blackRedTotal, float $rebatePercent): float
    {
        return $blackRedTotal < 0 ? round(abs($blackRedTotal) * $rebatePercent, 2) : 0;
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
