@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <h1 class="h3 mb-0">Dashboard</h1>
        <div class="text-muted small">Match by date and weekly detail</div>
    </div>
    <a href="{{ route('matches.create') }}" class="btn btn-primary">New Match</a>
</div>

<div class="card card-body mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h5 mb-0">System Summary</h2>
        <div class="text-muted small">All records</div>
    </div>
    <div class="row g-2">
        <div class="col-md">
            <div class="summary-tile">
                <div class="text-muted small">Bet Amount</div>
                <div class="summary-number"><x-money :value="$systemTotals['bet_amount']" /></div>
            </div>
        </div>
        <div class="col-md">
            <div class="summary-tile">
                <div class="text-muted small">Win/Lose</div>
                <div class="summary-number"><x-money :value="$systemTotals['black_red']" /></div>
            </div>
        </div>
        <div class="col-md">
            <div class="summary-tile">
                <div class="text-muted small">My Win/Lose</div>
                <div class="summary-number"><x-money :value="$systemTotals['my_winlose']" /></div>
            </div>
        </div>
        <div class="col-md">
            <div class="summary-tile">
                <div class="text-muted small">Rebate</div>
                <div class="summary-number"><x-money :value="$systemTotals['rebate_amount']" /></div>
            </div>
        </div>
        <div class="col-md">
            <div class="summary-tile">
                <div class="text-muted small">Run Tickets</div>
                <div class="summary-number"><x-money :value="$systemTotals['run_ticket']" /></div>
            </div>
        </div>
        <div class="col-md">
            <div class="summary-tile">
                <div class="text-muted small">Total</div>
                <div class="summary-number"><x-money :value="$systemTotals['net_total']" /></div>
            </div>
        </div>
    </div>
</div>

<form class="card card-body mb-3 py-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-4"><x-date-range /></div>
        <div class="col-md-2"><label class="form-label">Username</label><input name="username" value="{{ request('username') }}" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Match</label><input name="match" value="{{ request('match') }}" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Team</label><input name="team" value="{{ request('team') }}" class="form-control"></div>
        <div class="col-md-12 d-flex gap-2"><button class="btn btn-dark">Filter</button><a class="btn btn-outline-secondary" href="{{ route('dashboard') }}">Clear</a></div>
    </div>
</form>

<div class="card card-body mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h5 mb-0">Match by Date</h2>
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small">This week</span>
            <a href="{{ route('matches.index') }}" class="btn btn-sm btn-outline-secondary">All Matches</a>
        </div>
    </div>
    @forelse($matchByDate as $date => $matches)
        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered align-middle mb-0">
                <thead>
                    <tr class="table-success">
                        <th colspan="7">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>{{ $date }}</span>
                                <a class="btn btn-sm btn-outline-dark" href="{{ route('match-dates.show', $date) }}">Open Date Sheet</a>
                            </div>
                        </th>
                    </tr>
                    <tr>
                        <th>Match</th><th>Home</th><th>Away</th><th>Lines</th><th>Win/Lose</th><th>Run Tickets</th><th>Total</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($matches as $match)
                    @php
                        $matchWinLose = $match->entries->sum('black_red_amount');
                        $matchRun = $match->entries->sum('run_ticket');
                        $matchTotal = $match->entries->sum(fn($entry) => $entry->net_total);
                    @endphp
                    <tr>
                        <td><a class="fw-semibold" href="{{ route('matches.show', $match) }}">{{ $match->title }}</a></td>
                        <td>{{ $match->home_team }}</td>
                        <td>{{ $match->away_team }}</td>
                        <td>{{ $match->entries->count() }}</td>
                        <td><x-money :value="$matchWinLose" /></td>
                        <td><x-money :value="$matchRun" /></td>
                        <td><x-money :value="$matchTotal" /></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div class="text-muted">No matches found.</div>
    @endforelse
</div>

<div class="card card-body">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h5 mb-0">Weekly Detail</h2>
        <div class="text-muted small">All weeks</div>
    </div>

    @foreach($weeklySheets as $sheet)
    <div class="table-responsive mb-3">
        <table class="table table-sm table-bordered align-middle settlement-sheet mb-0">
            <thead>
                <tr class="table-success">
                    <th>{{ $sheet['label'] }}</th>
                    <th>Bet Amount</th>
                    @foreach($sheet['date_columns'] as $date)
                        <th>{{ $date->format('d/m') }}</th>
                    @endforeach
                    <th class="text-danger">Win/Lose</th>
                    <th class="text-danger percent-col">MY %</th>
                    <th class="text-danger">My Win/Lose</th>
                    <th class="percent-col">Rebate %</th>
                    <th>Rebate</th>
                    <th>Run Tickets</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
            @foreach($sheet['rows'] as $row)
                <tr>
                    <td><a href="{{ route('agents.history.show', $row['agent']) }}">{{ $row['agent']->username }}</a></td>
                    <td><x-money :value="$row['bet_amount']" /></td>
                    @foreach($sheet['date_columns'] as $date)
                        @php($dateKey = $date->format('Y-m-d'))
                        <td>
                            @if((float) $row['daily_amounts'][$dateKey] !== 0.0)
                                <x-money :value="$row['daily_amounts'][$dateKey]" />
                            @endif
                        </td>
                    @endforeach
                    <td><x-money :value="$row['black_red']" /></td>
                    <td class="percent-col">{{ number_format((float) $row['my_percent'], 4) }}</td>
                    <td><x-money :value="$row['my_winlose']" /></td>
                    <td class="percent-col">
                        <input
                            class="excel-input percent-input"
                            type="number"
                            step="0.0001"
                            value="{{ number_format((float) $row['rebate_percent'], 4, '.', '') }}"
                            data-weekly-rebate
                            data-agent-id="{{ $row['agent']->id }}"
                            data-week-start="{{ $sheet['week_start'] }}"
                        >
                    </td>
                    <td><x-money :value="$row['rebate_amount']" /></td>
                    <td><x-money :value="$row['run_ticket']" /></td>
                    <td><x-money :value="$row['net_total']" /></td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr class="table-warning fw-bold">
                    <td colspan="{{ 2 + count($sheet['date_columns']) }}">Total</td>
                    <td><x-money :value="$sheet['totals']['black_red']" /></td>
                    <td></td>
                    <td><x-money :value="$sheet['totals']['my_winlose']" /></td>
                    <td></td>
                    <td><x-money :value="$sheet['totals']['rebate_amount']" /></td>
                    <td><x-money :value="$sheet['totals']['run_ticket']" /></td>
                    <td><x-money :value="$sheet['totals']['net_total']" /></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-weekly-rebate]').forEach(input => {
    input.addEventListener('change', async event => {
        const field = event.target;
        field.classList.add('is-dirty');
        const response = await fetch('{{ route('weekly-rebates.update') }}', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                agent_id: field.dataset.agentId,
                week_start: field.dataset.weekStart,
                rebate_percent: field.value || 0,
            }),
        });

        if (!response.ok) {
            alert('Rebate % could not be saved.');
            field.classList.remove('is-dirty');
            return;
        }

        window.location.reload();
    });
});
</script>
@endpush
