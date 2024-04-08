<?php

use AnsiEscapesToHtml\Highlighter;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

new class extends Component {
    //curl --unix-socket /var/run/docker.sock http://localhost/v1.44/images/json
    public function swarm(): Collection
    {
        return Http::withOptions(['curl' => [CURLOPT_UNIX_SOCKET_PATH => '/var/run/docker.sock']])
            ->get('http://v1.44/swarm')
            ->collect();
    }

    public function stacks()
    {
//        return $this->services()->pluck('Spec.Labels')->map(fn($labels) => $labels['com.docker.stack.namespace'])->unique();
        return [];
    }

    public function servicesFrom(string $stack)
    {
        return $this->services()->filter(fn($service) => $service['Spec']['Labels']['com.docker.stack.namespace'] === $stack);
    }

    public function services()
    {
        return Http::withOptions(['curl' => [CURLOPT_UNIX_SOCKET_PATH => '/var/run/docker.sock']])
            ->get('http://v1.44/services')
            ->collect()
            ->map(function ($service) {
                $service['tasks'] = $this->tasks()->where('ServiceID', $service['ID'])?->groupBy('Slot') ?? [];

                return $service;
            });
    }

    public function nodes()
    {
        return Http::withOptions(['curl' => [CURLOPT_UNIX_SOCKET_PATH => '/var/run/docker.sock']])
            ->get('http://v1.44/nodes')
            ->collect();
    }

    #[Computed]
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
//        return [];

        return Http::withOptions(['curl' => [CURLOPT_UNIX_SOCKET_PATH => '/var/run/docker.sock']])
            ->get("http://v1.44/tasks/{$id}")
            ->collect();
    }

    public function taskLogs(string $id): string
    {
        return '';

        $body = Http::withOptions(['curl' => [CURLOPT_UNIX_SOCKET_PATH => '/var/run/docker.sock']])
            ->withQueryParameters(['stdout' => true, 'stderrout' => true])
            ->get("http://v1.44/tasks/{$id}/logs")
            ->body();

//        $body = str($body)->replace("\x01\x00\x00\x00\x00\x00\x00", "")->toString();

//        return (new Highlighter())->toHtml($body);
        //dd($body);

        return (new AnsiToHtmlConverter())->convert($body);
    }

    public function taskColorForState(string $state)
    {
        return match ($state) {
            'running' => 'badge-success',
            'shutdown' => 'badge-ghost',
            'failed' => 'badge-error',
            default => 'badge-outline',
        };
    }

    public function with(): array
    {
        return [
            'swarm' => $this->swarm(),
            'nodes' => $this->nodes(),
            'stacks' => $this->stacks(),
            'services' => $this->services(),
            'tasks' => $this->tasks(),
        ];
    }
} ?>

<div wire:poll.2s>
    <x-header title="Stacks" separator progress-indicator />

    @foreach($services as $service)
        <div class="border border-primary/30 p-5 mt-5" wire:key="service-{{ $service['ID'] }}">
            <div class="text-sm">{{ $service['Spec']['Labels']['com.docker.stack.namespace'] }}</div>
            <div class="font-bold">{{ $service['Spec']['Name'] }}</div>
            <div class="text-xs">Replicas: {{ $service['Spec']['Mode']['Replicated']['Replicas'] }}</div>

            {{--    TASKS    --}}
            @foreach($service['tasks'] as $slot => $tasks)
                <div wire:key="service-{{ $service['ID'] }}-slot-{{ rand() }}">
                    <hr class="my-5" />
                    <div class="font-bold">
                        {{ $slot }}
                    </div>

                    {{-- CURRENT --}}
                    <livewire:tasks.show :$service :task="$tasks->first()" wire:key="service-{{ $service['ID'] }}-task-main-{{ rand() }}" />

                    <hr class="my-5" />

                    {{-- PAST --}}
                    <div class="ml-5 pl-5 border-l-2">
                        @foreach($tasks->skip(1) as $k => $task)
                            <div class="mb-5" wire:key="service-{{ $service['ID'] }}-task-wrapper-{{ rand() }}">
                                <livewire:tasks.show :$service :$task wire:key="service-{{ $service['ID'] }}-task-{{ rand() }}" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
</div>
