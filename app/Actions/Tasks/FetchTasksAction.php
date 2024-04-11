<?php

namespace App\Actions\Tasks;

use App\Entities\Service;
use App\Entities\Task;
use App\Services\DockerSocketService;
use Illuminate\Support\Collection;

class FetchTasksAction extends DockerSocketService
{
    public function __construct(public Service $service)
    {
        parent::__construct();
    }

    public function execute(): Collection
    {
        return $this->get('/tasks', ['filters' => '{"service": ["' . $this->service->name . '"]}'])
            ->collect()
            ->transform(function ($task) {
                return (array) new Task(
                    id: $task['ID'],
                    service_id: $task['ServiceID'],
                    service_name: $this->service->name,
                    slot: $task['Slot'],
                    full_name: $this->service->name . '.' . $task['Slot'] . '.' . $task['ID'],
                    state: $task['Status']['State'],
                    desired_state: $task['DesiredState'],
                    error_message: $task['Status']['Err'] ?? null,
                    color: $this->taskColorForState($task['Status']['State']),
                    created_at: $task['CreatedAt'],
                );
            })
            ->sortBy([
                ['slot', 'asc'],
                ['created_at', 'desc']
            ])
            ->groupBy('slot') ?? collect();
    }

    public function taskColorForState(string $state)
    {
        return match ($state) {
            'running' => 'bg-success/40',
            'shutdown' => 'bg-base-200',
            'failed' => 'bg-error/40',
            default => 'bg-warning/40',
        };
    }
}
