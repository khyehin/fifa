<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function edit(Request $request)
    {
        $loginUsers = User::where('role', 'admin')
            ->orderBy('username')
            ->get();

        return view('settings.edit', [
            'user' => $request->user(),
            'loginUsers' => $loginUsers,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update($data);

        return back()->with('status', 'Login detail updated.');
    }

    public function requiredPassword(Request $request)
    {
        return view('settings.required-password', ['user' => $request->user()]);
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $request->user()->update([
            'password' => Hash::make($data['password']),
            'must_change_password' => false,
            'password_changed_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('status', 'Password updated.');
    }

    public function storeLoginUser(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'name' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        User::create([
            'username' => $data['username'],
            'name' => $data['name'] ?: $data['username'],
            'email' => $this->loginEmailFor($data['username']),
            'role' => 'admin',
            'password' => Hash::make($data['password']),
            'is_active' => $request->boolean('is_active', true),
            'must_change_password' => true,
            'my_percent' => 1,
        ]);

        return back()->with('status', 'Login user created.');
    }

    public function editLoginUser(Request $request, User $loginUser)
    {
        abort_unless($request->user()->isAdmin() && $loginUser->role === 'admin', 403);

        return view('settings.login-user-form', compact('loginUser'));
    }

    public function updateLoginUser(Request $request, User $loginUser)
    {
        abort_unless($request->user()->isAdmin() && $loginUser->role === 'admin', 403);

        $data = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($loginUser->id)],
            'name' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'confirmed', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
            'must_change_password' => ['nullable', 'boolean'],
        ]);

        $updates = [
            'username' => $data['username'],
            'name' => $data['name'] ?: $data['username'],
            'is_active' => $request->user()->is($loginUser) ? true : $request->boolean('is_active'),
            'must_change_password' => $request->boolean('must_change_password'),
        ];

        if (! empty($data['password'])) {
            $updates['password'] = Hash::make($data['password']);
            $updates['password_changed_at'] = null;
            $updates['must_change_password'] = true;
        }

        $loginUser->update($updates);

        return redirect()->route('settings.edit')->with('status', 'Login user updated.');
    }

    private function loginEmailFor(string $username): string
    {
        return str($username)->slug('_') . '_' . uniqid() . '@login.local';
    }
}
