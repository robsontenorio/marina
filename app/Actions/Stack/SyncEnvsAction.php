<?php

namespace App\Actions\Stack;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class SyncEnvsAction
{
    public function __construct(public string $stack, public Collection $envs, public Collection $previousEnvs)
    {
    }

    public function execute(): void
    {
        $this->envs->each(function ($env) {
            File::put(base_path(".data/stacks/{$this->stack}/{$env['name']}"), $env['content']);
        });

        $this->previousEnvs->each(function ($env) {
            if (! $this->envs->contains('name', $env['name'])) {
                File::delete(base_path(".data/stacks/{$this->stack}/{$env['name']}"));
            }
        });
    }
}
