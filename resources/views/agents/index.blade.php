@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <h1 class="h3 mb-0">Agents</h1>
        <div class="text-muted small">User defaults used when adding match rows</div>
    </div>
    <a href="{{ route('agents.create') }}" class="btn btn-primary">Add Agent</a>
</div>

<form class="card card-body mb-3">
    <div class="d-flex gap-2">
        <input name="q" value="{{ request('q') }}" class="form-control toolbar-input" placeholder="Search username or remarks">
        <button class="btn btn-dark">Search</button>
        <a class="btn btn-outline-secondary" href="{{ route('agents.index') }}">Clear</a>
    </div>
</form>

<div class="card card-body">
    <table class="table table-sm table-striped">
        <thead><tr><th>Username</th><th>Default Bet</th><th>MY %</th><th>Run Ticket</th><th>Locked</th><th>Status</th><th>Remarks</th><th></th></tr></thead>
        <tbody>
        @foreach ($agents as $agent)
            <tr>
                <td><a href="{{ route('agents.history.show', $agent) }}">{{ $agent->username }}</a></td>
                <td><x-money :value="$agent->default_bet_amount" /></td>
                <td>{{ number_format((float) $agent->my_percent, 4) }}</td>
                <td><x-money :value="$agent->run_ticket" /></td>
                <td>{{ $agent->bet_amount_locked ? 'Yes' : 'No' }}</td>
                <td><span class="badge status-pill {{ $agent->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $agent->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td>{{ $agent->remarks }}</td>
                <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('agents.edit', $agent) }}">Edit</a>
                    <form method="post" action="{{ route('agents.destroy', $agent) }}" class="d-inline" onsubmit="return confirm('Delete this agent?')">
                        @csrf @method('delete')
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{ $agents->links() }}
</div>
@endsection
