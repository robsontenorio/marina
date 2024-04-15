<?php

namespace App\Actions\Stack;

use Illuminate\Support\Facades\File;

class TrashEnvAction
{
    public function __construct(public string $stack, public string $fileName)
    {
    }

    public function execute(): void
    {
        File::delete(base_path(".data/stacks/{$this->stack}/{$this->fileName}"));
    }
}
