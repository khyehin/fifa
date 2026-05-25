@extends('layouts.app')

@section('content')
<div class="mb-2">
    <h1 class="h3 mb-0">Settings</h1>
    <div class="text-muted small">Change login detail and password</div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <form method="post" action="{{ route('settings.profile.update') }}" class="card card-body">
            @csrf @method('patch')
            <h2 class="h5">Login Detail</h2>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input name="username" value="{{ old('username', $user->username) }}" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input name="name" value="{{ old('name', $user->name) }}" class="form-control">
            </div>
            <button class="btn btn-primary">Save Login Detail</button>
        </form>
    </div>
    <div class="col-lg-6">
        <form method="post" action="{{ route('settings.password.update') }}" class="card card-body">
            @csrf @method('patch')
            <h2 class="h5">Password</h2>
            <div class="mb-3">
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <button class="btn btn-dark">Change Password</button>
        </form>
    </div>
</div>

@if($user->isAdmin())
<div class="card card-body mt-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h5 mb-0">Login Users</h2>
            <div class="text-muted small">Add and edit admin accounts</div>
        </div>
    </div>

    <form method="post" action="{{ route('settings.login-users.store') }}" class="row g-2 align-items-end mb-3">
        @csrf
        <div class="col-md-2">
            <label class="form-label">Username</label>
            <input name="username" value="{{ old('username') }}" class="form-control" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Name</label>
            <input name="name" value="{{ old('name') }}" class="form-control">
        </div>
        <div class="col-md-2">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Confirm</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>
        <div class="col-md-12 d-flex align-items-center gap-3">
            <label class="form-check mb-0">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" checked>
                <span class="form-check-label">Active</span>
            </label>
            <button class="btn btn-primary">Add Login User</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead>
                <tr><th>Username</th><th>Name</th><th>Role</th><th>Status</th><th>First Login Change Password</th><th></th></tr>
            </thead>
            <tbody>
            @foreach($loginUsers as $loginUser)
                <tr>
                    <td>{{ $loginUser->username }}</td>
                    <td>{{ $loginUser->name }}</td>
                    <td>{{ strtoupper($loginUser->role) }}</td>
                    <td>{{ $loginUser->is_active ? 'Active' : 'Inactive' }}</td>
                    <td>{{ $loginUser->must_change_password ? 'Yes' : 'No' }}</td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('settings.login-users.edit', $loginUser) }}">Edit</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
