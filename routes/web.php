<?php

use App\Jobs\FetchDockerStatsJob;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Users will be redirected to this route if not logged in
Volt::route('/login', 'login')->name('login');

// Register when accessing for the first time
Volt::route('/register', 'register');

// Define the logout
Route::get('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
});

Route::middleware('auth')->group(function () {
    Volt::route('/', 'dashboard');
    Volt::route('/stacks/{hash}/edit', 'stacks.edit');
    Volt::route('/stacks/create', 'stacks.create');
    Volt::route('/stacks/{hash}', 'stacks.show');
    Volt::route('/credentials', 'credentials.index');

    // Put `docker stats` in cache for a few seconds
    Route::get('/stats', function () {
        FetchDockerStatsJob::dispatchAfterResponse();
    });
});


