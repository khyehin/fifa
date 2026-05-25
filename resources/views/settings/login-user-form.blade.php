@extends('layouts.app')

@section('content')
<div class="mb-2">
    <h1 class="h3 mb-0">Edit Login User</h1>
    <div class="text-muted small">Reset password or update admin access</div>
</div>

<form method="post" action="{{ route('settings.login-users.update', $loginUser) }}" class="card card-body">
    @csrf @method('patch')
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Username</label>
            <input name="username" value="{{ old('username', $loginUser->username) }}" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Name</label>
            <input name="name" value="{{ old('name', $loginUser->name) }}" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">Role</label>
            <input value="ADMIN" class="form-control" disabled>
        </div>
        <div class="col-md-4">
            <label class="form-label">New Password</label>
            <input type="password" name="password" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="password_confirmation" class="form-control">
        </div>
        <div class="col-md-12 d-flex gap-4">
            <label class="form-check">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $loginUser->is_active)) @disabled(auth()->user()->is($loginUser))>
                <span class="form-check-label">Active</span>
            </label>
            <label class="form-check">
                <input type="checkbox" name="must_change_password" value="1" class="form-check-input" @checked(old('must_change_password', $loginUser->must_change_password))>
                <span class="form-check-label">Force change password on next login</span>
            </label>
        </div>
        <div class="col-md-12 d-flex gap-2">
            <button class="btn btn-primary">Save</button>
            <a class="btn btn-outline-secondary" href="{{ route('settings.edit') }}">Cancel</a>
        </div>
    </div>
</form>
@endsection
