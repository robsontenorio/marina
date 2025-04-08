<?php

namespace App\Actions\Services;

use App\Entities\Service;
use Illuminate\Support\Facades\Process;

class ForceServiceUpdateAction
{
    public function __construct(public Service $service)
    {
    }

    public function execute(): void
    {
        Process::path(base_path())->quietly()->start("docker service update --image {$this->service->image} --force {$this->service->name}");
    }
}
