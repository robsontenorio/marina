<?php

namespace App\Actions\Stack;

use Illuminate\Support\Facades\File;

class ScanStackAction
{
    public function __construct(public string $stack)
    {
    }

    public function execute(): string
    {
        return File::get(base_path("stacks/{$this->stack}/docker-compose.yml"));
    }
}
