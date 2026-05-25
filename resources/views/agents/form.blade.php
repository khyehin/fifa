@extends('layouts.app')

@section('content')
<h1 class="h3 mb-3">{{ $agent->exists ? 'Edit Agent' : 'Add Agent' }}</h1>

<form method="post" action="{{ $agent->exists ? route('agents.update', $agent) : route('agents.store') }}" class="card card-body">
    @csrf
    @if($agent->exists) @method('put') @endif
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label">Username</label><input name="username" value="{{ old('username', $agent->username) }}" class="form-control" required></div>
        <div class="col-md-2"><label class="form-label">Default Bet</label><input type="number" step="0.01" name="default_bet_amount" value="{{ old('default_bet_amount', $agent->default_bet_amount ?? 0) }}" class="form-control" required></div>
        <div class="col-md-2"><label class="form-label">MY %</label><input type="number" step="0.0001" name="my_percent" value="{{ old('my_percent', $agent->my_percent ?? 1) }}" class="form-control" required></div>
        <div class="col-md-2"><label class="form-label">Run Ticket</label><input type="number" step="0.01" name="run_ticket" value="{{ old('run_ticket', $agent->run_ticket ?? 0) }}" class="form-control" required></div>
        <div class="col-md-12"><label class="form-label">Remarks</label><textarea name="remarks" class="form-control" rows="3">{{ old('remarks', $agent->remarks) }}</textarea></div>
        <div class="col-md-12 d-flex gap-4">
            <label class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $agent->is_active ?? true))> <span class="form-check-label">Active</span></label>
            <label class="form-check"><input type="checkbox" name="bet_amount_locked" value="1" class="form-check-input" @checked(old('bet_amount_locked', $agent->bet_amount_locked ?? false))> <span class="form-check-label">Lock bet amount</span></label>
        </div>
        <div class="col-md-12 d-flex gap-2">
            <button class="btn btn-primary">Save</button>
            <a class="btn btn-outline-secondary" href="{{ route('agents.index') }}">Cancel</a>
        </div>
    </div>
</form>
@endsection
