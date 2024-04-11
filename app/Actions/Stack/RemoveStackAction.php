<?php

namespace App\Actions\Stack;

use Illuminate\Support\Facades\Process;

class RemoveStackAction
{
    public function __construct(public string $stack)
    {
    }

    public function execute(): void
    {
        Process::path(base_path())->quietly()->start("./docker stack rm {$this->stack}");
    }
}
