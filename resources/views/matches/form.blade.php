@extends('layouts.app')

@section('content')
<h1 class="h3 mb-3">{{ $match->exists ? 'Edit Match' : 'Add Match' }}</h1>

<form method="post" action="{{ $match->exists ? route('matches.update', $match) : route('matches.store') }}" class="card card-body">
    @csrf
    @if($match->exists) @method('put') @endif
    <div class="row g-3">
        <div class="col-md-2"><label class="form-label">Match Date</label><input type="date" name="match_date" value="{{ old('match_date', $match->match_date?->format('Y-m-d') ?? request('match_date', now()->format('Y-m-d'))) }}" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label">Match Title</label><input name="title" value="{{ old('title', $match->title) }}" class="form-control" placeholder="Slovakia vs Romania" required></div>
        <div class="col-md-3"><label class="form-label">Home Team</label><input name="home_team" value="{{ old('home_team', $match->home_team) }}" class="form-control" required></div>
        <div class="col-md-3"><label class="form-label">Away Team</label><input name="away_team" value="{{ old('away_team', $match->away_team) }}" class="form-control" required></div>
        <div class="col-md-12"><label class="form-label">Remarks</label><textarea name="remarks" class="form-control" rows="3">{{ old('remarks', $match->remarks) }}</textarea></div>
        <div class="col-md-12 d-flex gap-2">
            <button class="btn btn-primary">Save</button>
            <a class="btn btn-outline-secondary" href="{{ route('matches.index') }}">Cancel</a>
        </div>
    </div>
</form>
@endsection
