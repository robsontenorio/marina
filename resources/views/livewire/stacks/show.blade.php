<?php

use App\Actions\Services\FetchServicesAction;
use App\Actions\Services\ForceServiceUpdateAction;
use App\Actions\Services\RemoveServiceAction;
use App\Actions\Services\ScaleDownServicesAction;
use App\Actions\Services\ScaleUpServicesAction;
use App\Actions\Stack\DeployStackAction;
use App\Actions\Stack\RemoveStackAction;
use App\Actions\Stack\TrashStackAction;
use App\Actions\Tasks\FetchTasksAction;
use App\Entities\Service;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

new class extends Component {
    use Toast;

    public string $stack = '';

    public string $process_output = '';

    public array $updating = [];

    public ?Collection $services;

    public function mount(string $stack): void
    {
        $this->stack = $stack;
    }

    public function boot(): void
    {
        $this->services = (new FetchServicesAction($this->stack))->execute();
    }
}; ?>

<div wire:poll>
    <x-header :title="$stack" separator>
        <x-slot:actions>
            <x-button label="Edit" icon="o-pencil" link="/stacks/{{ str($stack)->toBase64 }}/{{ $stack }}/edit" responsive />
        </x-slot:actions>
    </x-header>

    @foreach($services as $service)
        <x-card x-data="{expand: false}" @click="expand = !expand" shadow class="mb-5 border border-base-100 hover:!border-primary cursor-pointer"
                wire:key="service-{{ $service['id'] }}">

            <livewire:services.show :$service :$services wire:key="service-{{ $service['id'] }}-component" />

            {{-- SLOTS    --}}
            <div class="hidden cursor-default pt-10" :class="expand && '!block'" @click.stop="">
                @foreach($service['tasks'] as $slot => $tasks)
                    <div x-data="{expandTask: false}" @click.stop="expandTask = !expandTask" class="cursor-pointer" wire:key="service-{{ $service['id'] }}-slot">
                        <div>
                            <hr />
                            <div class="flex justify-between gap-3 hover:bg-base-200/50 hover:rounded p-3" :class="expandTask && 'bg-base-200/50'">
                                {{-- CURRENT--}}
                                <div class="flex-1">
                                    <livewire:tasks.show :task="$tasks->first()" wire:key="service-{{ $service['id'] }}-task-main-{{ rand() }}" />
                                </div>
                            </div>

                            {{-- HISTORY--}}
                            <div class="ml-11 mt-5 mb-10 cursor-default hidden" :class="expandTask && '!block'" @click.stop="">
                                <x-icon name="o-arrow-up" label="Task history" class="h-4 w-4 text-xs text-gray-400 -ml-2 mr-8" />
                                @forelse($tasks->skip(1) as $k => $task)
                                    <x-timeline-item title="" :last="$loop->last" pending>
                                        <x-slot:title>
                                            <div class="font-normal">
                                                <span class="badge badge-sm {{ $task['color'] }} mr-2">{{ $task['state'] }}</span>
                                                <span @class(["hidden", "!inline-block" => $task['is_updating']])>
                                                    <x-loading class="loading-ring loading-xs" />
                                                </span>
                                                <span class="text-xs text-gray-500 tooltip"
                                                      data-tip="{{ Carbon::parse($task['created_at'])->format('Y-m-d H:i:s') }}">{{ $task['created_at'] }}</span>
                                            </div>
                                        </x-slot:title>
                                    </x-timeline-item>
                                @empty
                                    <x-timeline-item title="-" subtitle="No previous task" last="true" pending />
                                @endforelse
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
            <x-icon name="o-server-stack" class="w-10 h-10 bg-amber-200 p-2 rounded-full text-neutral" />
            <div class="text-xl pl-3 mt-3">No services deployed.</div>
            {{--            <div class="text-sm text-gray-500 mt-3">Have you hit the deploy button?</div>--}}
        </div>
    @endif
</div>
