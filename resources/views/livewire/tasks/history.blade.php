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
    @forelse($tasks as $task)
        <x-timeline-item title="" :last="$loop->last" pending wire:key="task-{{ $task->id }}">
            <x-slot:title>
                <div class="font-normal">
                    <span class="badge badge-sm {{ $task->color() }} mr-2">{{ $task->state }}</span>
                    <span class="text-xs">{{ Carbon::parse($task->created_at)->format('Y-m-d H:i:s') }}</span>
                    <span @class(["hidden", "!inline-block" => $task->isUpdating()])>
                        <x-loading class="loading-ring" />
                    </span>
                    <span class="text-xs block sm:inline-flex">{{ $task->error_message }}</span>
                </div>
            </x-slot:title>
        </x-timeline-item>
    @empty
        <x-timeline-item title="-" subtitle="No previous task" last="true" pending />
    @endforelse
</div>
