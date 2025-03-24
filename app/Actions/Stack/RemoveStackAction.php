<?php

namespace App\Actions\Stack;

use App\Traits\RunsLoggableCommand;
use Livewire\Volt\Component;

class RemoveStackAction
{
    use RunsLoggableCommand;

    public function __construct(public string $stack, protected Component $component, public string $target)
    {
    }

    public function execute(): void
    {
        $this->run("docker stack rm {$this->stack}");
    }
}
