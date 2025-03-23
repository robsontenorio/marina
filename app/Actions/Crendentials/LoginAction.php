<?php

namespace App\Actions\Crendentials;

use Illuminate\Support\Facades\Process;

class LoginAction
{
    public function __construct(public array $data)
    {
    }

    public function execute(): void
    {
        Process::run("echo {$this->data['access_token']} | docker login {$this->data['url']} -u {$this->data['username']} --password-stdin");
    }
}
