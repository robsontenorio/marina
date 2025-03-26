<?php

namespace App\Actions\Stack;

use Illuminate\Support\Facades\File;

class UpdateStackAction
{
    public function __construct(public string $stack, public string $content, public ?string $originalName = null)
    {
    }

    public function execute(): void
    {
        if ($this->originalName && $this->originalName != $this->stack) {
            new RenameStackAction($this->originalName, $this->stack)->execute();
        }

        File::put(base_path(".data/stacks/{$this->stack}/docker-compose.yml"), $this->content);
    }
}
