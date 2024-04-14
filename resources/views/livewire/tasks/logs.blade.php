<?php

use App\Actions\Tasks\ScanTaskLogsAction;
use App\Entities\Task;
use Livewire\Volt\Component;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

new class extends Component {
    public Task $task;

    public mixed $logs;

    public bool $show = false;

    public function getLogs()
    {
        $this->show = true;
        $logs = (new ScanTaskLogsAction($this->task))->execute();
        $this->logs = (new AnsiToHtmlConverter())->convert($logs);
    }
}; ?>

<div @click.stop="">
    <x-button tooltip="Logs" wire:click.stop="getLogs" icon="o-command-line" class="btn-ghost btn-xs btn-circle mb-2" />

    <x-modal wire:model="show" box-class="max-w-screen-xl relative bg-black relative">
        <div class="top-0 right-10 sticky text-right">
            <x-button label="Close" @click="$wire.show = false" class="btn-sm " />
        </div>
        <div class="-m-8 ">
            <pre class="rounded-lg overflow-auto overflow-x-hidden p-10 text-xs">{!! $logs !!}</pre>
        </div>
    </x-modal>
</div>
