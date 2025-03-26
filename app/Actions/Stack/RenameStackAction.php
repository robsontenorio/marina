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
        $isDifferent = $this->newName != $this->currentName;
        $newNameExists = File::exists(base_path(".data/stacks/{$this->newName}/docker-compose.yml"));

        if ($isDifferent && $newNameExists) {
            throw  ValidationException::withMessages(['stack' => 'This stack already exists.']);
        }

        new RemoveStackAction($this->currentName)->execute();

        File::moveDirectory(base_path(".data/stacks/{$this->currentName}"), base_path(".data/stacks/{$this->newName}"));
    }
}
