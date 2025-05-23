<?php

use App\Actions\Tasks\ScanTaskLogsAction;
use App\Entities\Task;
use Carbon\Carbon;
use Livewire\Attributes\Reactive;
use Livewire\Volt\Component;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

new class extends Component {
    #[Reactive]
    public Task $task;

    public bool $show = false;

    public function logs(): void
    {
        $this->show = true;
        new ScanTaskLogsAction($this->task)->execute();
    }
}; ?>

<div>
    <div>
        <div @class(["flex justify-between items-center gap-3 h-12"])>
            <div class="flex-1 flex gap-3 items-center">
                <div class="tooltip" data-tip="{{ str($task->name)->after('_') }}">
                    <x-icon name="o-cube" class="mb-1" />
                    <span class="font-bold hidden sm:inline-flex">{{ str($task->name)->after('_') }}</span>
                </div>

                <x-badge class="badge-sm me-2 w-0 text-transparent sm:w-fit sm:text-inherit {{ $task->color() }}" :value="$task->state" />

                <span class="text-xs hidden sm:inline-flex">{{ Carbon::parse($task->created_at)->format('Y-m-d H:i:s') }}</span>

                <span data-tip="This task is updating" @class(["hidden", "tooltip !inline-fkex mb-3" => $task->isUpdating()])>
                    <x-loading class="loading-ring loading-xs -mb-2" />
                </span>

                <div @class(["hidden", "text-xs text-error !inline-flex" => $task->error_message ?? ''])>
                    {{ $task->error_message ?? '' }}
                </div>
            </div>

            {{--  Removing warning --}}
            <div @class(["hidden", "!inline-flex" => $task->willRemove()])>
                <x-badge value="removing" class="badge-sm bg-error/40" />
            </div>

            {{--  Stats --}}
            <div @class(["hidden", "!inline-flex" => $task->isRunning()])>
                <span class="lg:tooltip" data-tip="cpu / mem">
                    <x-icon name="o-cpu-chip" label="{{ $task->stats->cpu ?? '-' }} / {{ $task->stats->memory ?? '-' }}" class="text-xs" />
                </span>
            </div>

            {{--  Logs --}}
            <div @class(["hidden mb-2", "!inline-flex" => $task->isRunning() || $task->isUpdating()])>
                <x-button wire:click.stop="logs" icon="o-command-line" tooltip="Logs" class="btn-ghost btn-sm btn-circle" />
            </div>
        </div>
    </div>

    @if($show)
        <div wire:poll="logs"></div>
        <livewire:logs wire:model="show" @close="$toggle('show')" />
    @endif
</div>
