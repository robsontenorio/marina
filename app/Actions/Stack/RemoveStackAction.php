<?php

namespace App\Actions\Stack;

use App\Traits\RunsLoggableCommand;

class RemoveStackAction
{
    use RunsLoggableCommand;

    public function __construct(public string $stack)
    {
    }

    public function execute(): void
    {
        $this->run("docker stack rm {$this->stack}");
    }
}
