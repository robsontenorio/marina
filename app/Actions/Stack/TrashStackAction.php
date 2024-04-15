<?php

namespace App\Actions\Stack;

use Illuminate\Support\Facades\File;

class TrashStackAction
{
    public function __construct(public string $stack)
    {
    }

    public function execute(): void
    {
        (new RemoveStackAction($this->stack))->execute();

        File::deleteDirectory(base_path(".data/stacks/{$this->stack}"));
    }
}
