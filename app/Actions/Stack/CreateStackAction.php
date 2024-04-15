<?php

namespace App\Actions\Stack;

use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

class CreateStackAction
{
    public function __construct(public string $stack, public string $content)
    {
    }

    public function execute(): void
    {
        if (File::exists(base_path(".data/stacks/{$this->stack}/docker-compose.yml"))) {
            throw  ValidationException::withMessages(['stack' => 'This directory already exists.']);
        }

        File::makeDirectory(base_path(".data/stacks/{$this->stack}"));
        File::put(base_path(".data/stacks/{$this->stack}/docker-compose.yml"), $this->content);
    }
}
