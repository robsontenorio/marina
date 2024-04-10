<?php

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Livewire\Volt\Component;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

new class extends Component {
    public string $stack = '';

    public string $process_output = '';

    public ?Collection $services;

    public ?Collection $tasks;

    public ?Collection $stats;

    public array $updating = [];

    public function mount(string $stack): void
    {
        $this->stack = $stack;
    }

    public function boot()
    {
        $this->tasks = $this->tasks();
        $this->stats = $this->fetchStats();
        $this->services = $this->services();
    }

    public function remove(): void
    {
        Process::path(base_path())->quietly()->start("./docker stack rm {$this->stack}");
    }

    public function deploy(): void
    {
        Process::path(base_path())->quietly()->start("./docker stack deploy -c stacks/{$this->stack}/docker-compose.yml {$this->stack} --with-registry-auth");
    }

    public function fetchStats()
    {
        return collect(Cache::get('joe-stats', []));
    }

    public function scaleUpService(string $id)
    {
        $service = $this->services()->firstwhere('ID', $id);
        $name = $service['Spec']['Name'];
        $replicas = $service['Spec']['Mode']['Replicated']['Replicas'] + 1;
        $this->updating[] = $id;

        $process = Process::path(base_path())->quietly()->start("./docker service scale {$name}={$replicas}");
    }

    public function scaleDown(string $id)
    {
        $service = $this->services()->firstwhere('ID', $id);
        $name = $service['Spec']['Name'];
        $replicas = $service['Spec']['Mode']['Replicated']['Replicas'] - 1;
        $this->updating[] = $id;

        $process = Process::path(base_path())->quietly()->start("./docker service scale {$name}={$replicas}");
    }

    public function forceUpdate(string $id)
    {
        $service = $this->services()->firstwhere('ID', $id);
        $name = $service['Spec']['Name'];

        $this->updating[] = $id;

        $process = Process::path(base_path())->quietly()->start("./docker service update --force {$name}");
    }

    public function services()
    {
        return Http::withOptions(['curl' => [CURLOPT_UNIX_SOCKET_PATH => '/var/run/docker.sock']])
            ->withQueryParameters([
                'status' => true,
                'filters' => '{"label": ["com.docker.stack.namespace=' . $this->stack . '"]}'
            ])
            ->get("http://v1.44/services")
            ->collect()
            ->map(function ($service) {
                $service['tasks'] = $this->tasks
                    ->where('ServiceID', $service['ID'])
                    ->map(function ($task) use ($service) {
                        $task['container_id'] = $service['Spec']['Name'] . '.' . $task['Slot'] . '.' . $task['ID'];
                        $task['stats'] = $this->stats->firstWhere('name', $task['container_id']);

                        return $task;
                    })
                    ?->groupBy('Slot') ?? [];

                return $service;
            })
            ->map(function ($service) {
                $service['stats'] = [
                    'cpu' => $this->calculateCPU($service['tasks']),
                    'mem' => $this->calculateMemory($service['tasks'])
                ];

                $service['is_running'] = $service['tasks']->flatten(1)->where('Status.State', 'running')->isNotEmpty();
                $service['is_updating'] = $service['tasks']
                    ->flatten(1)
                    ->filter(function ($task) {
                        return ($task['Status']['State'] != $task['DesiredState']) && $task['Status']['State'] != 'failed';
                    })
                    ->isNotEmpty();

                if (! $service['is_updating']) {
                    $this->updating = Arr::where($this->updating, fn($id) => $id != $service['ID']);
                }

                return $service;
            });
    }

    public function calculateCPU(Collection $tasks)
    {
        $isEmpty = $tasks
                ->flatten(1)
                ->where('Status.State', 'running')
                ->pluck('stats.cpu')
                ->unique()
                ->first() == null;

        if ($isEmpty) {
            return '-';
        }

        return $tasks
                ->flatten(1)
                ->where('Status.State', 'running')
                ->pluck('stats.cpu')
                ->map(fn($cpu) => str($cpu)->replace('%', '')->toFloat())
                ->whenEmpty(fn() => collect(['-']))
                ->sum() . '%';
    }

    public function calculateMemory(Collection $tasks)
    {
        $kilobytesFromGigas = $tasks
            ->flatten(1)
            ->where('Status.State', 'running')
            ->pluck('stats.mem')
            ->filter(fn($mem) => str($mem)->contains('GiB'))
            ->map(fn($cpu) => str($cpu)->replace('GiB', '')->toFloat() * 1024 * 1024)
            ->sum();

        $kilobytesFromMegas = $tasks
            ->flatten(1)
            ->where('Status.State', 'running')
            ->pluck('stats.mem')
            ->filter(fn($mem) => str($mem)->contains('MiB'))
            ->map(fn($cpu) => str($cpu)->replace('MiB', '')->toFloat() * 1024)
            ->sum();

        $kilobytes = $tasks
            ->flatten(1)
            ->where('Status.State', 'running')
            ->pluck('stats.mem')
            ->filter(fn($mem) => str($mem)->contains('KiB'))
            ->map(fn($cpu) => str($cpu)->replace('KiB', '')->toFloat())
            ->sum();

        $total = $kilobytesFromGigas + $kilobytesFromMegas + $kilobytes;

        if ($total == 0) {
            return '-';
        }

        if ($total < 1024) {
            return $total . 'KiB';
        }

        if ($total > 1024 && $total < 1024 * 1024) {
            return round($total / 1024, 2) . 'MiB';
        }

        return round($total / (1024 * 1024), 2) . 'GiB';
    }

    public function tasks()
    {
        return Http::withOptions(['curl' => [CURLOPT_UNIX_SOCKET_PATH => '/var/run/docker.sock']])
            ->get('http://v1.44/tasks')
            ->collect()
            ->map(function ($task) {
                $task['color'] = $this->taskColorForState($task['Status']['State']);
                $task['inspect'] = $this->inspectTask($task['ID']);
                $task['logs'] = $this->taskLogs($task['ID']);
                $task['diff_timestamp'] = Carbon::parse($task['CreatedAt'])->diffForHumans();

                return $task;
            })
            ->sortBy([
                ['Slot', 'asc'],
                ['CreatedAt', 'desc']
            ]);
    }

    public function inspectTask(string $id)
    {
        return Http::withOptions(['curl' => [CURLOPT_UNIX_SOCKET_PATH => '/var/run/docker.sock']])
            ->get("http://v1.44/tasks/{$id}")
            ->json();
    }

    public function taskColorForState(string $state)
    {
        return match ($state) {
            'running' => 'bg-success/40',
            'shutdown' => 'bg-base-200',
            'failed' => 'bg-error/40',
            default => 'bg-warning/40',
        };
    }

    public function taskLogs(string $id): string
    {
        return '';

        $body = Http::withOptions(['curl' => [CURLOPT_UNIX_SOCKET_PATH => '/var/run/docker.sock']])
            ->withQueryParameters(['stdout' => true, 'stderrout' => true])
            ->get("http://v1.44/tasks/{$id}/logs")
            ->body();

        return (new AnsiToHtmlConverter())->convert($body);
    }
}; ?>

<div wire:poll>
    <x-header :title="$stack" separator>
        <x-slot:actions>
            <x-button
                label="Remove"
                wire:click="remove"
                wire:confirm="Are you sure?"
                tooltip-left="Runs `docker stack rm {{ $stack }}`"
                icon="o-bookmark-slash"
                spinner
                responsive />

            <x-button label="Edit" icon="o-pencil" link="/stacks/{{ $stack }}/edit" responsive />

            <x-button
                label="Re-deploy"
                tooltip-left="Runs `docker stack deploy {{ $stack }}`"
                wire:click="deploy" class="btn-primary"
                icon="o-fire"
                spinner
                responsive />
        </x-slot:actions>
    </x-header>

    @foreach($services as $service)
        <x-card x-data="{expand: false}" @click="expand = !expand" shadow class="mb-5 border border-base-100 hover:!border-primary cursor-pointer"
                wire:key="service-{{ $service['ID'] }}">

            <div class="flex justify-between">
                <div class="flex-1">
                    <div class="flex gap-3">
                        {{--  REPLICAS--}}
                        <div class="flex gap-3">
                            <div @class(["bg-base-300 text-base-content rounded-lg text-center py-2 px-3", "!bg-success !text-base-100"  => $service['is_running']])>
                                <div class="font-black">{{ $service['Spec']['Mode']['Replicated']['Replicas'] }}</div>
                                <div class="text-xs">replicas</div>
                            </div>
                            <div class="grid">
                                <x-button
                                    tooltip="Scale Up"
                                    wire:click.stop="scaleUpService('{{ $service['ID'] }}')"
                                    class="btn-ghost btn-sm btn-circle"
                                    icon="o-chevron-up"
                                    spinner />

                                <x-button
                                    tooltip="Scale Down"
                                    wire:click.stop="scaleDown('{{ $service['ID'] }}')"
                                    :disabled="$service['Spec']['Mode']['Replicated']['Replicas'] == 0"
                                    icon="o-chevron-down"
                                    class="btn-ghost btn-sm btn-circle"
                                    spinner />
                            </div>
                        </div>
                        <div>
                            {{--  SERVICE --}}
                            <div class="font-black text-xl mb-3">
                                {{ $service['Spec']['Name'] }}
                                <span data-tip="This service is updating" @class(["hidden tooltip", "!inline-block" => $service['is_updating'] || in_array($service['ID'], $updating)]) />
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
                        tooltip-left="Force update this service"
                        wire:click.stop="forceUpdate('{{ $service['ID'] }}')"
                        icon="o-fire"
                        class="btn-ghost btn-sm btn-circle"
                        spinner />
                </div>
            </div>

            {{--    SLOTS    --}}
            <div class="hidden cursor-default pt-10" :class="expand && '!block'" @click.stop="">
                @foreach($service['tasks'] as $slot => $tasks)
                    <div x-data="{expandTask: false}" @click.stop="expandTask = !expandTask" class="cursor-pointer" wire:key="service-{{ $service['ID'] }}-slot">
                        <div>
                            <hr />
                            <div class="flex justify-between gap-3 hover:bg-base-200/50 hover:rounded p-3" :class="expandTask && 'bg-base-200/50'">
                                {{-- CURRENT --}}
                                <div class="flex-1">
                                    <livewire:tasks.show :current="true" :$service :task="$tasks->first()" wire:key="service-{{ $service['ID'] }}-task-main-{{ rand() }}" />
                                </div>
                            </div>

                            {{-- HISTORY --}}
                            <div class="ml-11 mt-5 mb-10 cursor-default hidden" :class="expandTask && '!block'" @click.stop="">
                                <x-icon name="o-arrow-up" label="Task history" class="h-4 w-4 text-xs text-gray-400 -ml-2 mr-8" />
                                @forelse($tasks->skip(1) as $k => $task)
                                    {{--                                    <div class="mb-5" wire:key="service-{{ $service['ID'] }}-task-wrapper-{{ rand() }}">--}}
                                    {{--                                        <livewire:tasks.show :$service :$task wire:key="service-{{ $service['ID'] }}-task-{{ rand() }}" />--}}
                                    {{--                                    </div>--}}


                                    <x-timeline-item title="" :last="$loop->last" pending>
                                        <x-slot:title>
                                            <div class="font-normal">
                                                <span class="badge badge-sm {{ $task['color'] }} mr-2">{{ $task['Status']['State'] }}</span>
                                                <span @class(["hidden" => ($task['DesiredState'] == $task['Status']['State']) || (($task['Status']['Err'] ??  '') != '')])>
                                                <x-loading class="loading-ring loading-xs" />
                                            </span>
                                                <span class="text-xs text-gray-500 tooltip"
                                                      data-tip="{{ Carbon::parse($task['CreatedAt'])->format('Y-m-d H:i:s') }}">{{ $task['diff_timestamp'] }}</span>
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
