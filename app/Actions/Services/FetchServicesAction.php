<?php

namespace App\Actions\Services;

use App\Entities\Service;
use App\Services\DockerSocketService;
use Illuminate\Support\Collection;

class FetchServicesAction extends DockerSocketService
{
    public function __construct(public ?string $stack = null)
    {
        parent::__construct();
    }

    public function execute(): Collection
    {
        return $this->get("/services", [
            'status' => true,
            'filters' => '{"label": ["com.docker.stack.namespace=' . $this->stack . '"]}'
        ])
            ->collect()
            ->transform(function ($service) {
                return (array) new Service(
                    id: $service['ID'],
                    name: $service['Spec']['Name'],
                    replicas: $service['Spec']['Mode']['Replicated']['Replicas'],
                );
            });
    }
}
