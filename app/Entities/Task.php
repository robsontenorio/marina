<?php

namespace App\Entities;

use App\Traits\WireMe;
use Illuminate\Support\Facades\Cache;
use Livewire\Wireable;

class Task implements Wireable
{
    use WireMe;

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
        public ?string $created_at = null,
        public Stats $stats = new Stats(),
    ) {
        $this->name = $this->name ?? $this->service_name . '.' . $this->slot;
        $this->stats = $this->stats();
    }

    public function isUpdating(): bool
    {
        return $this->state != $this->desired_state
            && ! in_array($this->state, [self::STATE_REJECTED, self::STATE_FAILED])
            && $this->error_message == null;
    }

    public function isRunning(): bool
    {
        return $this->state == self::STATE_RUNNING;
    }

    public function willRemove(): bool
    {
        return $this->desired_state == self::STATE_REMOVE;
    }

    public function stats(): Stats
    {
        $stats = collect(Cache::get('joe-stats', fn() => []))->firstWhere('name', $this->full_name);

        return new Stats($stats['cpu'] ?? '', $stats['mem'] ?? '');
    }

    public function color()
    {
        return match ($this->state) {
            'running' => 'bg-success/40',
            'shutdown' => 'bg-base-200',
            'failed' => 'bg-error/40',
            'rejected' => 'bg-error/40',
            default => 'bg-warning/40',
        };
    }
}
