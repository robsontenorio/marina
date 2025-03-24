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
        new ScanTaskLogsAction($this->task, $this, 'logs')->execute();
    }
}; ?>

<div>
    <div>
        <div @class(["flex justify-between items-center gap-3"])>
            <div>
                <x-icon name="o-cube" />
            </div>
            <div class="flex-1 flex gap-3 items-center">
                <span class="font-bold">{{ str($task->name)->after('_') }}</span>

                <x-badge class="badge-sm mr-2 {{ $task->color() }}" :value="$task->state" />

                <span class="text-xs">{{ Carbon::parse($task->created_at)->format('Y-m-d H:i:s') }}</span>
                
                <span data-tip="This task is updating" @class(["hidden", "tooltip !inline-block mb-3" => $task->isUpdating()])>
                    <x-loading class="loading-ring -mb-2" />
                </span>

                <div @class(["hidden", "text-xs text-error !block" => $task->error_message ?? ''])>
                    {{ $task->error_message ?? '' }}
                </div>
            </div>

            {{--  Removing warning --}}
            <div @class(["hidden", "!inline-flex" => $task->willRemove()])>
                <x-badge value="removing" class="badge-sm bg-error/40" />
            </div>

            {{--  Stats --}}
            <div @class(["hidden", "!inline-flex" => $task->isRunning()])>
                <span class="tooltip" data-tip="cpu / mem">
                    <x-icon name="o-cpu-chip" label="{{ $task->stats->cpu ?? '-' }} / {{ $task->stats->memory ?? '-' }}" class="text-xs" />
                </span>
            </div>

            {{--  Logs --}}
            <div @class(["hidden", "!inline-flex" => $task->isRunning() || $task->isUpdating()])>
                <x-button wire:click.stop="logs" icon="o-command-line" tooltip="Logs" class="btn-ghost btn-sm btn-circle mb-2" />
            </div>
        </div>
    </div>

    @if($show)
        <div wire:poll="logs"></div>
        <livewire:logs wire:model="show" @close="$toggle('show')" />
    @endif
</div>
