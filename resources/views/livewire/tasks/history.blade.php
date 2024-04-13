<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Reactive;
use Livewire\Volt\Component;

new class extends Component {
    #[Reactive]
    public Collection $tasks;
}; ?>

<div>
    @forelse($tasks as $k => $task)
        <x-timeline-item title="" :last="$loop->last" pending wire:key="task-{{ $task['id'] }}">
            <x-slot:title>
                <div class="font-normal">
                    <span class="badge badge-sm {{ $task['color'] }} mr-2">{{ $task['state'] }}</span>
                    <span @class(["hidden", "!inline-block" => $task['is_updating']])>
                        <x-loading class="loading-ring -mb-2" />
                    </span>
                    <span class="text-xs text-gray-500 tooltip" data-tip="{{ Carbon::parse($task['created_at'])->format('Y-m-d H:i:s') }}">{{ $task['created_at'] }}</span>
                </div>
            </x-slot:title>
        </x-timeline-item>
    @empty
        <x-timeline-item title="-" subtitle="No previous task" last="true" pending />
    @endforelse
</div>
