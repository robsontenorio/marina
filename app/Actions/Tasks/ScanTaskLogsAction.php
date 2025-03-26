<?php

namespace App\Actions\Tasks;

use App\Entities\Task;
use App\Traits\RunsLoggableCommand;

class ScanTaskLogsAction
{
    use RunsLoggableCommand;

    public function __construct(public Task $task)
    {
    }

    public function execute()
    {
        $this->run("docker service logs {$this->task->id} --raw");
    }
}
