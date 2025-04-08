<?php

namespace App\Actions\Credentials;

use Exception;
use Illuminate\Support\Facades\Process;

class LoginAction
{
    public function __construct(public array $data)
    {
    }

    public function execute(): void
    {
        $this->data['url'] = str($this->data['url'])->contains("docker.io") ? null : $this->data['url'];

        $command = "echo {$this->data['access_token']} | docker login {$this->data['url']} -u {$this->data['username']} --password-stdin";

        Process::path(base_path())->run($command, function (string $type, string $output) {
            if ($type == 'err') {
                throw new Exception($output);
            }
        });
    }
}
