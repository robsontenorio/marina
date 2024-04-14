<?php

namespace App\Actions\Tasks;

use App\Entities\Task;
use Illuminate\Support\Facades\Process;

class ScanTaskLogsAction
{
    public function __construct(public Task $task)
    {
    }

    public function execute()
    {
        return Process::run("docker service logs {$this->task->id} --raw")->output();
    }
}
