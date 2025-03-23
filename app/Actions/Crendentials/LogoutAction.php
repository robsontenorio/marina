<?php

namespace App\Actions\Crendentials;

use Illuminate\Support\Facades\Process;

class LogoutAction
{
    public function __construct(public string $url)
    {
    }

    public function execute(): void
    {
        Process::run("docker logout {$this->url}");
    }
}
