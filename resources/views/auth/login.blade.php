@extends('layouts.app')

@section('content')
<div class="login-page">
    <div class="card login-card">
        <div class="card-body">
            <div class="login-title">Welcome back</div>
            <div class="login-subtitle">Sign in to continue</div>
            <form method="post" action="{{ route('login.store') }}" class="vstack gap-3">
                @csrf
                <div>
                    <label class="form-label">Username</label>
                    <input name="username" value="{{ old('username') }}" class="form-control" autofocus required>
                </div>
                <div>
                    <label class="form-label">Password</label>
                    <input name="password" type="password" class="form-control" required>
                </div>
                <button class="btn btn-primary w-100 py-2">Login</button>
            </form>
        </div>
    </div>
</div>
@endsection
