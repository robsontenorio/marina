<?php

use App\Jobs\FetchDockerStatsJob;
use Livewire\Volt\Volt;

Volt::route('/', 'users.index');

Volt::route('/stacks', 'stacks.index');

