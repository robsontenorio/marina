<?php

namespace App\Entities;

use Illuminate\Support\Facades\Cache;

class Task
{
    public function __construct(
        public string $id,
        public string $service_id,
        public string $service_name,
        public int $slot,
        public ?string $name = null,
        public ?string $full_name = null,
        public ?string $state = null,
        public ?string $desired_state = null,
        public ?string $error_message = null,
        public ?string $color = null,
        public ?bool $is_running = false,
        public ?bool $is_updating = false,
        public ?bool $will_remove = false,
        public ?string $created_at = null,
        public ?array $stats = [
            'cpu' => '',
            'memory' => '',
        ],
    ) {
        $this->name = $this->name();
        $this->is_running = $this->isRunning();
        $this->is_updating = $this->isUpdating();
        $this->will_remove = $this->willRemove();
        $this->stats = $this->stats();
    }

    public function name(): string
    {
        return $this->name ?? $this->service_name . '.' . $this->slot;
    }

    public function isUpdating(): bool
    {
        // TODO: usar ENUM
        return $this->state != $this->desired_state && $this->state != 'failed';
    }

    public function isRunning(): bool
    {
        // TODO: usar ENUM
        return $this->state == 'running';
    }

    public function willRemove(): bool
    {
        // TODO: usar ENUM
        return $this->desired_state == 'remove';
    }

    public function stats(): array
    {
        $stats = collect(Cache::get('joe-stats', fn() => []))->firstWhere('name', $this->full_name);

        return [
            'cpu' => $stats['cpu'] ?? '',
            'mem' => $stats['mem'] ?? '',
        ];
    }
}
