@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <h1 class="h3 mb-0">Matches</h1>
        <div class="text-muted small">Daily matches and entries</div>
    </div>
    <a href="{{ route('matches.create') }}" class="btn btn-primary">Add Match</a>
</div>

<form class="card card-body mb-3">
    <div class="d-flex flex-wrap gap-2">
        <div class="toolbar-input"><x-date-range /></div>
        <input name="q" value="{{ request('q') }}" class="form-control toolbar-input" placeholder="Match or team">
        <button class="btn btn-dark">Search</button>
        <a class="btn btn-outline-secondary" href="{{ route('matches.index') }}">Clear</a>
    </div>
</form>

<div class="card card-body">
    @php
        $dateOrder = request('date_order') === 'asc' ? 'asc' : 'desc';
        $nextDateOrder = $dateOrder === 'asc' ? 'desc' : 'asc';
    @endphp
    <table class="table table-sm table-striped">
        <thead>
            <tr>
                <th>
                    <a class="text-decoration-none text-reset" href="{{ route('matches.index', array_merge(request()->except('page'), ['date_order' => $nextDateOrder])) }}">
                        Date {{ $dateOrder === 'asc' ? 'Oldest First' : 'Latest First' }}
                    </a>
                </th>
                <th>Title</th><th>Home</th><th>Away</th><th>Rows</th><th>Remarks</th><th></th>
            </tr>
        </thead>
        <tbody>
        @foreach ($matches as $match)
            <tr>
                <td>{{ $match->match_date->format('Y-m-d') }}</td>
                <td><a href="{{ route('matches.show', $match) }}">{{ $match->title }}</a></td>
                <td>{{ $match->home_team }}</td>
                <td>{{ $match->away_team }}</td>
                <td>{{ $match->entries_count }}</td>
                <td>{{ $match->remarks }}</td>
                <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('matches.edit', $match) }}">Edit</a>
                    <form method="post" action="{{ route('matches.destroy', $match) }}" class="d-inline" onsubmit="return confirm('Delete this match and entries?')">
                        @csrf @method('delete')
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{ $matches->links() }}
</div>
@endsection
