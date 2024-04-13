<?php

namespace App\Actions\Support;

use Illuminate\Support\Collection;

class CalculateMemoryAction
{
    public function __construct(public Collection $tasks)
    {
    }

    public function execute(): string
    {
        $kilobytesFromGigas = $this->tasks
            ->flatten(1)
            ->where('state', 'running')
            ->pluck('stats.memory')
            ->filter(fn($mem) => str($mem)->contains('GiB'))
            ->map(fn($cpu) => str($cpu)->replace('GiB', '')->toFloat() * 1024 * 1024)
            ->sum();

        $kilobytesFromMegas = $this->tasks
            ->flatten(1)
            ->where('state', 'running')
            ->pluck('stats.memory')
            ->filter(fn($mem) => str($mem)->contains('MiB'))
            ->map(fn($cpu) => str($cpu)->replace('MiB', '')->toFloat() * 1024)
            ->sum();

        $kilobytes = $this->tasks
            ->flatten(1)
            ->where('state', 'running')
            ->pluck('stats.memory')
            ->filter(fn($mem) => str($mem)->contains('KiB'))
            ->map(fn($cpu) => str($cpu)->replace('KiB', '')->toFloat())
            ->sum();

        $total = $kilobytesFromGigas + $kilobytesFromMegas + $kilobytes;

        if ($total == 0) {
            return '-';
        }

        if ($total < 1024) {
            return $total . 'KiB';
        }

        if ($total > 1024 && $total < 1024 * 1024) {
            return round($total / 1024, 2) . 'MiB';
        }

        return round($total / (1024 * 1024), 2) . 'GiB';
    }
}
