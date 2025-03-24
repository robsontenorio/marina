<?php

namespace App\Actions\Services;

use App\Entities\Service;
use App\Traits\RunsLoggableCommand;
use Livewire\Volt\Component;

class ScanServiceLogsAction
{
    use RunsLoggableCommand;

    public function __construct(public Service $service, protected Component $component, public string $target)
    {
    }

    public function execute()
    {
        $this->run("docker service logs {$this->service->name} --raw");
    }
}
