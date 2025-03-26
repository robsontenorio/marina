<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class DockerSocketService
{
    public const string VERSION = '_';

    public const string SOCKET_PATH = '/var/run/docker.sock';

    public function __construct()
    {
    }

    public function get(string $url, array $query = []): Collection
    {
        $response = $this->prepare()->get($url, $query)->collect();

        // If there is a `message` ignore it and return an empty collection
        return $response->keys()->first() == 'message' ? collect() : $response;
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
