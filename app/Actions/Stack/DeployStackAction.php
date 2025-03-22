<?php

namespace App\Actions\Stack;

use Illuminate\Support\Facades\Process;

class DeployStackAction
{
    public function __construct(public string $stack) {}

    public function execute(): void
    {
        Process::path(base_path())->quietly()->start("docker stack deploy -c .data/stacks/{$this->stack}/docker-compose.yml {$this->stack} --with-registry-auth");
    }
}
