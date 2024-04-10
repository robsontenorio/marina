<?php

use Livewire\Volt\Volt;

Volt::route('/', 'users.index');

Volt::route('/all-stacks', 'stacks.index');

Volt::route('/stacks/{stack}/edit', 'stacks.edit');
Volt::route('/stacks/{stack}', 'stacks.show');
