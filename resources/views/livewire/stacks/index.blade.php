<?php

use App\Actions\Services\FetchServicesAction;
use App\Actions\Services\ForceServiceUpdateAction;
use App\Actions\Services\RemoveServiceAction;
use App\Actions\Services\ScaleDownServicesAction;
use App\Actions\Services\ScaleUpServicesAction;
use App\Actions\Stack\DeployStackAction;
use App\Actions\Stack\RemoveStackAction;
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

    public function boot(): void
    {
        $this->services = (new FetchServicesAction($this->stack))->execute();
    }

    // Remove stack
    public function remove(): void
    {
        (new RemoveStackAction($this->stack))->execute();

        $this->success('Running command ...', position: 'toast-bottom', timeout: 5000);
    }

    // Deploy stack
    public function deploy(): void
    {
        (new DeployStackAction($this->stack))->execute();

        $this->success('Running command ...', position: 'toast-bottom', timeout: 5000);
    }

    // Scale service up
    public function scaleUp(string $id): void
    {
        $service = new Service(...$this->services->firstWhere('id', $id));

        (new ScaleUpServicesAction($service))->execute();

        $this->success('Running command ...', position: 'toast-bottom', timeout: 5000);
    }

    // Scale service down
    public function scaleDown(string $id): void
    {
        $service = new Service(...$this->services->firstWhere('id', $id));

        (new ScaleDownServicesAction($service))->execute();

        $this->success('Running command ...', position: 'toast-bottom', timeout: 5000);
    }

    // Force update service
    public function forceUpdate(string $id): void
    {
        $service = new Service(...$this->services->firstWhere('id', $id));

        (new ForceServiceUpdateAction($service))->execute();

        $this->success('Running command ...', position: 'toast-bottom', timeout: 5000);
    }

    // Remove service
    public function removeService(string $id): void
    {
        $service = new Service(...$this->services->firstWhere('id', $id));

        (new RemoveServiceAction($service))->execute();

        $this->success('Running command ...', position: 'toast-bottom', timeout: 5000);
    }
}; ?>

<div wire:poll>
    <x-header title="All stacks" separator>
        <x-slot:actions>
            <x-button label="Add stack" link="/stacks/create" icon="o-plus" class="btn-primary" responsive />
        </x-slot:actions>
    </x-header>

    @foreach($services as $service)
        <x-card x-data="{expand: false}" @click="expand = !expand" shadow class="mb-5 border border-base-100 hover:!border-primary cursor-pointer"
                wire:key="service-{{ $service['id'] }}">

            <div class="flex justify-between">
                <div class="flex-1">
                    <div class="flex gap-3">
                        {{--  REPLICAS--}}
                        <div class="flex gap-3">
                            <div @class(["bg-base-300 text-base-content rounded-lg text-center py-2 px-3", "!bg-success !text-base-100"  => $service['is_running']])>
                                <div class="font-black">{{ $service['replicas'] }}</div>
                                <div class="text-xs">replicas</div>
                            </div>
                            <div class="grid">
                                <x-button
                                    tooltip="Scale Up"
                                    wire:click.stop="scaleUp('{{ $service['id'] }}')"
                                    class="btn-ghost btn-sm btn-circle"
                                    icon="o-chevron-up"
                                    spinner />

                                <x-button
                                    tooltip="Scale Down"
                                    wire:click.stop="scaleDown('{{ $service['id'] }}')"
                                    :disabled="$service['replicas'] == 0"
                                    icon="o-chevron-down"
                                    class="btn-ghost btn-sm btn-circle"
                                    spinner />
                            </div>
                        </div>
                        <div>
                            {{--  SERVICE --}}
                            <div class="font-black text-xl mb-3">
                                {{ $service['name'] }}
                                <span data-tip="This service is updating" @class(["hidden", "tooltip !inline-block" => $service['is_updating']]) >
                                    <x-loading class="loading-ring loading-xs" />
                                </span>
                            </div>

                            {{--  STATS--}}
                            <div>
                                <span class="tooltip" data-tip="cpu / mem">
                                    <x-icon name="o-cpu-chip" label="{{ $service['stats']['cpu'] ?? '-' }} / {{ $service['stats']['mem'] ?? '-' }}" class="text-xs" />
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <x-button
                        tooltip-left="`docker service rm {service}`"
                        wire:click.stop="removeService('{{ $service['id'] }}')"
                        wire:confirm="Are you sure?"
                        icon="o-bookmark-slash"
                        class="btn-ghost btn-sm btn-circle"
                        spinner />

                    <x-button
                        tooltip-left="`docker service update --force {service}`"
                        wire:click.stop="forceUpdate('{{ $service['id'] }}')"
                        wire:confirm="Are you sure?"
                        icon="o-fire"
                        class="btn-ghost btn-sm btn-circle"
                        spinner />
                </div>
            </div>

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
</div>
