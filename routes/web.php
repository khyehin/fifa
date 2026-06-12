<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FootballMatchController;
use App\Http\Controllers\MatchDateController;
use App\Http\Controllers\MatchEntryController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserSettlementController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::post('/settings/login-users', [SettingsController::class, 'storeLoginUser'])->name('settings.login-users.store');
    Route::get('/settings/login-users/{loginUser}/edit', [SettingsController::class, 'editLoginUser'])->name('settings.login-users.edit');
    Route::patch('/settings/login-users/{loginUser}', [SettingsController::class, 'updateLoginUser'])->name('settings.login-users.update');
    Route::get('/settings/change-password', [SettingsController::class, 'requiredPassword'])->name('settings.password.required');
    Route::patch('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');

    Route::middleware('password.changed')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::patch('/weekly-rebates', [DashboardController::class, 'updateWeeklyRebate'])->name('weekly-rebates.update');

        Route::get('/agents/search', [AgentController::class, 'search'])->name('agents.search');
        Route::post('/agents/quick-create', [AgentController::class, 'quickCreate'])->name('agents.quick-create');
        Route::resource('agents', AgentController::class)->except('show');

        Route::resource('matches', FootballMatchController::class);
        Route::get('/match-dates/{date}', [MatchDateController::class, 'show'])->name('match-dates.show');
        Route::post('/matches/{match}/entries', [MatchEntryController::class, 'store'])->name('matches.entries.store');
        Route::patch('/entries/{entry}', [MatchEntryController::class, 'update'])->name('entries.update');
        Route::delete('/entries/{entry}', [MatchEntryController::class, 'destroy'])->name('entries.destroy');

        Route::get('/agents/{agent}/history', [UserSettlementController::class, 'show'])->name('agents.history.show');
    });
});
