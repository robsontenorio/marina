<?php

namespace App\Entities;

use Illuminate\Support\Facades\Cache;

class Task
{
    public const STATE_RUNNING = 'running';

    public const STATE_FAILED = 'failed';

    public const STATE_REJECTED = 'rejected';

    public const STATE_REMOVE = 'remove';

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
        $this->color = $this->colorFor($this->state);
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
        return $this->state != $this->desired_state && ! in_array($this->state, [self::STATE_REJECTED, self::STATE_FAILED]);
    }

    public function isRunning(): bool
    {
        // TODO: usar ENUM
        return $this->state == self::STATE_RUNNING;
    }

    public function willRemove(): bool
    {
        // TODO: usar ENUM
        return $this->desired_state == self::STATE_REMOVE;
    }

    public function stats(): array
    {
        $stats = collect(Cache::get('joe-stats', fn() => []))->firstWhere('name', $this->full_name);

        return [
            'cpu' => $stats['cpu'] ?? '',
            'mem' => $stats['mem'] ?? '',
        ];
    }

    public function colorFor(string $state)
    {
        return match ($state) {
            'running' => 'bg-success/40',
            'shutdown' => 'bg-base-200',
            'failed' => 'bg-error/40',
            'rejected' => 'bg-error/40',
            default => 'bg-warning/40',
        };
    }
}
