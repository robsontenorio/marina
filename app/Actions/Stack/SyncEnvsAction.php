<?php

namespace App\Actions\Stack;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class SyncEnvsAction
{
    public function __construct(public string $stack, public Collection $envs)
    {
    }

    public function execute(): void
    {
        // Delete existing `env` files
        new ScanEnvsAction($this->stack)->execute()->each(function ($env) {
            File::delete(base_path(".data/stacks/{$this->stack}/{$env['name']}"));
        });

        // Save the new `env` files
        $this->envs->each(function ($env) {
            File::put(base_path(".data/stacks/{$this->stack}/{$env['name']}"), $env['content']);
        });
    }
}
