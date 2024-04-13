<?php

use Illuminate\Support\Collection;
use Livewire\Attributes\Reactive;
use Livewire\Volt\Component;

new class extends Component {
    #[Reactive]
    public array $stats;

    #[Reactive]
    public Collection $services;
}; ?>

<div>
    <div class="grid grid-cols-4 gap-5 mb-8">
        <x-stat title="CPU" :value="$stats['cpu']" icon="o-cpu-chip" class="shadow" color="text-base-content" />
        <x-stat title="Memory" :value="$stats['mem']" icon="o-rectangle-stack" class="shadow" color="text-base-content" />
        <x-stat title="Services" :value="$services->count()" icon="o-squares-2x2" class="shadow" color="text-base-content" />
        <x-stat title="Tasks" :value="$services->pluck('tasks')->flatten(1)->count()" icon="o-cube" class="shadow" color="text-base-content" />
    </div>
</div>
