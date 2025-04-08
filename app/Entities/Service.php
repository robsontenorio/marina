<?php

namespace App\Entities;

use App\Actions\Support\CalculateCPUAction;
use App\Actions\Support\CalculateMemoryAction;
use App\Actions\Tasks\FetchTasksAction;
use App\Traits\WireMe;
use Illuminate\Support\Collection;
use Livewire\Wireable;

class Service implements Wireable
{
    use WireMe;

    public function __construct(
        public string $id,
        public string $name,
        public int $replicas = 0,
        public ?string $image = null,
        public Stats $stats = new Stats(),
        public Collection $tasks = new Collection(),
    ) {
        $this->tasks = new FetchTasksAction($this)->execute();
        $this->stats = $this->stats();
    }

    public function isRunning(): bool
    {
        return $this->tasks
            ->flatten(1)
            ->filter(fn(Task $task) => $task->isRunning())
            ->isNotEmpty();
    }

    public function isUpdating(): bool
    {
        return $this->tasks
            ->flatten(1)
            ->filter(fn(Task $task) => $task->isUpdating())
            ->isNotEmpty();
    }

    public function stats(): Stats
    {
        return new Stats(
            new CalculateCPUAction($this->tasks)->execute(),
            new CalculateMemoryAction($this->tasks)->execute(),
        );
    }
}
