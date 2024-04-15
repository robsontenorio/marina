<?php

namespace App\Actions\Stack;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class ScanEnvsAction
{
    public function __construct(public string $stack)
    {
    }

    public function execute(): Collection
    {
        $envs = File::glob(base_path(".data/stacks/{$this->stack}/.env*"), true);

        return collect($envs)->map(function ($env) {
            return [
                'name' => basename($env),
                'content' => File::get($env)
            ];
        });
    }
}
