<?php

use Livewire\Volt\Volt;

Volt::route('/', 'dashboard');

Volt::route('/all-stacks', 'stacks.index');

Volt::route('/stacks/{hash}/{stack}/edit', 'stacks.edit');
Volt::route('/stacks/create', 'stacks.create');
Volt::route('/stacks/{hash}/{stack}', 'stacks.show');

