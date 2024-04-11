<?php

namespace App\Actions\Support;

use Illuminate\Support\Collection;

class CalculateCPUAction
{
    public function __construct(public Collection $tasks)
    {
    }

    public function execute(): string
    {
        $isEmpty = $this->tasks
                ->flatten(1)
                ->where('state', 'running')
                ->pluck('stats.cpu')
                ->unique()
                ->first() == null;

        if ($isEmpty) {
            return '-';
        }

        return $this->tasks
                ->flatten(1)
                ->where('state', 'running')
                ->pluck('stats.cpu')
                ->map(fn($cpu) => str($cpu)->replace('%', '')->toFloat())
                ->whenEmpty(fn() => collect(['-']))
                ->sum() . '%';
    }
}
