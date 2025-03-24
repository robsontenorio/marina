<?php

namespace App\Actions\Stack;

use App\Traits\RunsLoggableCommand;
use Livewire\Volt\Component;

class DeployStackAction
{
    use RunsLoggableCommand;

    public function __construct(public string $stack, protected Component $component, public string $target)
    {
    }

    public function execute(): void
    {
        $this->run("docker stack deploy -c .data/stacks/{$this->stack}/docker-compose.yml --detach=true --resolve-image=always --prune --with-registry-auth {$this->stack}");
    }
}
