<?php

namespace App\Entities;

use App\Actions\Support\CalculateCPUAction;
use App\Actions\Support\CalculateMemoryAction;
use App\Actions\Tasks\FetchTasksAction;
use Illuminate\Support\Collection;

class Service
{
    public function __construct(
        public string $id,
        public string $name,
        public int $replicas = 0,
        public bool $is_running = false,
        public bool $is_updating = false,
        public array $stats = [],
        public Collection $tasks = new Collection(),
    ) {
        $this->tasks = (new FetchTasksAction($this))->execute();
        $this->is_running = $this->isRunning();
        $this->is_updating = $this->isUpdating();
        $this->stats = $this->stats();
    }

    public function isRunning(): bool
    {
        return $this->tasks
            ->flatten(1)
            ->filter(fn($task) => $task['is_running'])
            ->isNotEmpty();
    }

    public function isUpdating(): bool
    {
        return $this->tasks
            ->flatten(1)
            ->filter(fn($task) => $task['is_updating'])
            ->isNotEmpty();
    }

    public function stats(): array
    {
        return [
            'cpu' => (new CalculateCPUAction($this->tasks))->execute(),
            'mem' => (new CalculateMemoryAction($this->tasks))->execute(),
        ];
    }
}
