<?php

namespace App\Actions\Tasks;

use App\Entities\Task;
use Illuminate\Support\Facades\Process;

class ScanTaskLogsAction
{
    public function __construct(public Task $task) {}

    public function execute()
    {
        $process = Process::run("/var/www/app/docker service logs {$this->task->id} --raw");

        return $process->failed() ? $process->errorOutput() : $process->output();
    }
}
