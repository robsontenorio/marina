<?php

namespace App\Actions\Tasks;

use App\Entities\Task;
use App\Traits\RunsLoggableCommand;
use Livewire\Volt\Component;

class ScanTaskLogsAction
{
    use RunsLoggableCommand;

    public function __construct(public Task $task, protected Component $component, public string $target)
    {
    }

    public function execute()
    {
        $this->run("docker service logs {$this->task->id} --raw");
    }
}
