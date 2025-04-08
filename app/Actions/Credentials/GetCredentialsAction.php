<?php

namespace App\Actions\Credentials;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Process;
use stdClass;

class GetCredentialsAction
{
    private array $registries = [
        'docker.io' => 'fab.docker',
        'ghcr.io' => 'fab.github',
        'gitlab.com' => 'fab.gitlab',
    ];

    public function __construct()
    {
    }

    public function execute(): Collection
    {
        $registries = Process::run("docker-credential-pass list")->output();
        $registries = json_decode($registries, true);

        return collect($registries)->map(function ($username, $url) {
            $object = new stdClass();
            $object->url = $url;
            $object->username = $username;
            $object->domain = $this->domain($url);
            $object->icon = $this->icon($url);

            return $object;
        })->flatten();
    }

    private function domain(string $url): string
    {
        $domain = parse_url($url);
        $domain = str($domain['host'] ?? $domain['path']);

        return $domain->substrCount('.') == 1 ? $domain : $domain->after('.');
    }

    private function icon(string $url): string
    {
        return $this->registries[$this->domain($url)] ?? 'o-square-3-stack-3d';
    }
}
