<?php

use App\Actions\Tasks\ScanTaskLogsAction;
use App\Entities\Task;
use Livewire\Attributes\Reactive;
use Livewire\Volt\Component;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

new class extends Component {
    #[Reactive]
    public Task $task;

    public bool $show = false;

    public mixed $logs;

    public function fetchLogs(): void
    {
        $logs = new ScanTaskLogsAction($this->task)->execute();
        $this->logs = new AnsiToHtmlConverter()->convert($logs);
    }
}; ?>

<div @click.stop="">
    <x-button tooltip="Logs" wire:click="$toggle('show')" icon="o-command-line" class="btn-ghost btn-xs btn-circle mb-2" spinner />

    @if($show)
        <div wire:poll.1500ms="fetchLogs"></div>
    @endif

    <x-modal wire:model="show" box-class="max-w-screen-lg bg-black">
        <div class="top-0 right-10 sticky text-right">
            <x-button label="Close" wire:click="$toggle('show')" class="btn-sm " />
        </div>
        <pre class="rounded-lg overflow-y-auto overflow-x-hidden text-xs whitespace-break-spaces">{!! $logs !!}</pre>

        <div class="grid gap-5 m-10">
            @if($task->isUpdating() || $logs === null)
                <x-loading class="loading-dots text-base-100 " />
            @endif

            @if($logs != null)
                <div class="text-base-100">
                    Task state:
                    <x-badge class="badge-sm {{ $task->color() }} text-base-100" :value="$task->state" />
                </div>
            @endif
        </div>
    </x-modal>
</div>
