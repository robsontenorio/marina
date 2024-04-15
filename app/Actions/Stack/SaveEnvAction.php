<?php

namespace App\Actions\Stack;

use Illuminate\Support\Facades\File;

class SaveEnvAction
{
    public function __construct(public string $stack, public string $fileName, public string $content)
    {
    }

    public function execute(): void
    {
        File::put(base_path(".data/stacks/{$this->stack}/{$this->fileName}"), $this->content);
    }
}
