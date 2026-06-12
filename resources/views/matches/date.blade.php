@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Match Sheet - {{ $date }}</h1>
        <div class="text-muted">All matches and user lines for this date</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('matches.create', ['match_date' => $date]) }}" class="btn btn-primary">Add Match</a>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Dashboard</a>
    </div>
</div>

@forelse($matches as $match)
    @php
        $totalBet = $match->entries->sum('bet_amount');
        $totalWinLose = $match->entries->sum('black_red_amount');
        $totalMyWinLose = $match->entries->sum('my_winlose');
        $totalRun = $match->entries->sum('run_ticket');
        $totalNet = $match->entries->sum(fn($entry) => $entry->net_total);
    @endphp
    <div class="card card-body mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="h5 mb-0">{{ $match->title }}</h2>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('matches.show', $match) }}">Open / Add Line</a>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
                <thead>
                    <tr class="table-success">
                        <th>User</th>
                        <th>Bet Amount</th>
                        <th>H/A</th>
                        <th>O/U</th>
                        <th>Black H/O, Red A/U</th>
                        <th class="percent-col">MY %</th>
                        <th>My Win/Lose</th>
                        <th>Run Tickets</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($match->entries->sortBy(fn($entry) => $entry->agent->username) as $entry)
                    <tr>
                        <td><a href="{{ route('agents.history.show', $entry->agent) }}">{{ $entry->agent->username }}</a></td>
                        <td><x-money :value="$entry->bet_amount" /></td>
                        <td>{{ $entry->ha }}</td>
                        <td>{{ $entry->ou }}</td>
                        <td><x-money :value="$entry->black_red_amount" /></td>
                        <td class="percent-col">{{ number_format((float) $entry->my_percent, 4) }}</td>
                        <td><x-money :value="$entry->my_winlose" /></td>
                        <td><x-money :value="$entry->run_ticket" /></td>
                        <td><x-money :value="$entry->net_total" /></td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-warning fw-bold">
                        <td>Total</td>
                        <td><x-money :value="$totalBet" /></td>
                        <td></td>
                        <td></td>
                        <td><x-money :value="$totalWinLose" /></td>
                        <td></td>
                        <td><x-money :value="$totalMyWinLose" /></td>
                        <td><x-money :value="$totalRun" /></td>
                        <td><x-money :value="$totalNet" /></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@empty
    <div class="card card-body text-muted">No matches for this date.</div>
@endforelse
@endsection
