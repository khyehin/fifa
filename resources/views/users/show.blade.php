@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $agent->username }}</h1>
        <div class="text-muted">Full betting history</div>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('agents.index') }}">Agents</a>
</div>

<form class="card card-body mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-4"><x-date-range /></div>
        <div class="col-md-3"><label class="form-label">Match</label><input name="match" value="{{ request('match') }}" class="form-control"></div>
        <div class="col-md-3 d-flex gap-2"><button class="btn btn-dark">Filter</button><a class="btn btn-outline-secondary" href="{{ route('agents.history.show', $agent) }}">Clear</a></div>
    </div>
</form>

<div class="row g-3 mb-3">
    @foreach (['bet_amount' => 'Total Bet', 'black_red' => 'Total Win/Loss', 'my_winlose' => 'Total My W/L', 'rebate_amount' => 'Rebate', 'run_ticket' => 'Run Tickets', 'net_total' => 'Net Total'] as $key => $label)
        <div class="col-md">
            <div class="card card-body">
                <div class="text-muted small">{{ $label }}</div>
                <div class="summary-number"><x-money :value="$totals[$key]" /></div>
            </div>
        </div>
    @endforeach
</div>

<div class="card card-body">
    <table class="table table-sm table-striped" data-datatable>
        <thead><tr><th>Date</th><th>Match</th><th>Bet</th><th>H/A</th><th>O/U</th><th>Win/Loss</th><th>MY %</th><th>My W/L</th><th>Run</th><th>Net</th></tr></thead>
        <tbody>
        @foreach($entries as $entry)
            <tr>
                <td>{{ $entry->footballMatch->match_date->format('Y-m-d') }}</td>
                <td><a href="{{ route('matches.show', $entry->footballMatch) }}">{{ $entry->footballMatch->title }}</a></td>
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
    </table>
</div>
@endsection
