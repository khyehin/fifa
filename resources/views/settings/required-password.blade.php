@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <form method="post" action="{{ route('settings.password.update') }}" class="card card-body">
            @csrf @method('patch')
            <h1 class="h4">Change Password Required</h1>
            <p class="text-muted">First login must change password before using the dashboard.</p>
            <div class="mb-3">
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" minlength="8" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="form-control" minlength="8" required>
            </div>
            <button class="btn btn-primary w-100">Change Password</button>
        </form>
    </div>
</div>
@endsection
