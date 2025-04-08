<?php

namespace App\Actions\Stack;

use Illuminate\Support\Facades\Crypt;

class DeployStackFromWebhookAction
{
    public function __construct(public string $hash)
    {
    }

    public function execute(): void
    {
        $stack = Crypt::decryptString($this->hash);
        new DeployStackAction($stack)->execute();
    }
}
