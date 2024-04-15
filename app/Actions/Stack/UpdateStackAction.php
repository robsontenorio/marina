<?php

namespace App\Actions\Stack;

use Illuminate\Support\Facades\File;

class UpdateStackAction
{
    public function __construct(public string $stack, public string $content)
    {
    }

    public function execute(): void
    {
        File::put(base_path(".data/stacks/{$this->stack}/docker-compose.yml"), $this->content);
    }
}
