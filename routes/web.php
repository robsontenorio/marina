<?php

use App\Jobs\FetchDockerStatsJob;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/', 'users.index');

Volt::route('/stacks', 'stacks.index');

Route::get('/stats', function () {
    FetchDockerStatsJob::dispatchAfterResponse();
});
