<?php

namespace App\Actions\Stack;

use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

class RenameStackAction
{
    public function __construct(public string $currentName, public string $newName)
    {
    }

    public function execute(): void
    {
        if (File::exists(base_path("stacks/{$this->newName}/docker-compose.yml"))) {
            throw  ValidationException::withMessages(['stack' => 'This directory already exists.']);
        }

        File::moveDirectory(base_path("stacks/{$this->currentName}"), base_path("stacks/{$this->newName}"));
    }
}
