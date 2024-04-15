<?php

namespace App\Services;

use App\Exceptions\CaxumbaException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class DockerSocketService
{
    public const VERSION = 'v1.44';

    public const SOCKET_PATH = '/var/run/docker.sock';

    public function __construct()
    {
    }

    public function get(string $url, array $query = null)
    {
        return $this->prepare()->get($url, $query);
    }

    public function prepare(): PendingRequest
    {
        return Http::baseUrl("http://" . self::VERSION)
            ->withOptions([
                'curl' => [
                    CURLOPT_UNIX_SOCKET_PATH => self::SOCKET_PATH
                ]
            ]);
    }
}
