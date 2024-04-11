<?php

namespace App\Actions\Services;

use App\Entities\Service;
use Illuminate\Support\Facades\Process;

class ScaleDownServicesAction
{
    public function __construct(public Service $service)
    {
    }

    public function execute(): void
    {
        $replicas = --$this->service->replicas;

        Process::path(base_path())->quietly()->start("./docker service scale {$this->service->name}={$replicas}");
    }
}
