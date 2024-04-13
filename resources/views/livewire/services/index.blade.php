<?php

use Illuminate\Support\Collection;
use Livewire\Attributes\Reactive;
use Livewire\Volt\Component;

new class extends Component {
    #[Reactive]
    public Collection $services;
}; ?>

<div>
    @foreach($services as $service)
        <x-card
            x-data="{expand: false}"
            @click="expand = !expand"
            wire:key="service-{{ $service['id'] }}"
            class="mb-5 border border-base-100 hover:!border-primary cursor-pointer"
            shadow
        >
            {{-- SERVICE --}}
            <livewire:services.show :$service :$services wire:key="service-{{ $service['id'] }}-component" />

            {{-- TASKS  --}}
            <div class="hidden cursor-default pt-10" :class="expand && '!block'" @click.stop="">
                @foreach($service['tasks'] as $slot => $tasks)
                    <div x-data="{expandTask: false}" @click.stop="expandTask = !expandTask" class="cursor-pointer" wire:key="service-{{ $service['id'] }}-slot">
                        <div>
                            <hr />
                            <div class="hover:bg-base-200/50 hover:rounded p-3" :class="expandTask && 'bg-base-200/50'">
                                {{-- CURRENT TASK--}}
                                <livewire:tasks.show :task="$tasks->first()" wire:key="service-{{ $service['id'] }}-task-main-{{ rand() }}" />
                            </div>

                            {{-- TASK HISTORY--}}
                            <div class="ml-11 mt-5 mb-10 cursor-default hidden" :class="expandTask && '!block'" @click.stop="">
                                <x-icon name="o-arrow-up" label="Task history" class="h-4 w-4 text-xs text-gray-400 -ml-2 mr-8" />
                                <livewire:tasks.history :tasks="$tasks->skip(1)" wire:key="service-{{ $service['id'] }}-task-history-{{ rand() }}" />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    @endforeach

    @if($services->isEmpty())
        {{-- TODO: create a new component `x-????` --}}
        <div class="text-center">
            <x-icon name="o-squares-2x2" class="w-10 h-10 bg-amber-200 p-2 rounded-full text-neutral" />
            <div class="text-xl pl-3 mt-3">No services deployed.</div>
            {{--            <div class="text-sm text-gray-500 mt-3">Have you hit the deploy button?</div>--}}
        </div>
    @endif
</div>
