<?php

namespace App\Actions\Services;

use App\Entities\Service;
use Illuminate\Support\Facades\Process;

class RemoveServiceAction
{
    public function __construct(public Service $service) {}

    public function execute(): void
    {
        Process::path(base_path())->quietly()->start("./docker service rm {$this->service->name}");
    }
}
