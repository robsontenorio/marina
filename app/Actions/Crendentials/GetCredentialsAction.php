<?php

namespace App\Actions\Crendentials;

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
        $registries = Process::run("cat ~/.docker/config.json")->output();
        $registries = json_decode($registries, true);
        $registries = $registries['auths'] ?? [];

        return collect($registries)->map(function ($registry, $key) {
            $object = new stdClass();
            $object->url = $key;
            $object->domain = $this->domain($key);
            $object->username = $this->username($registry['auth']);
            $object->icon = $this->icon($key);

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
        return $this->registries[$this->domain($url)] ?? 'o-cube-transparent';
    }

    private function username(string $auth): string
    {
        return str(base64_decode($auth))->before(':');
    }
}
