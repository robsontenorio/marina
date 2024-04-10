<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;

class FethDockerStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'joe:docker-stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parses `docker stats` output and put it in cache for the dashboard';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $output = Process::path(base_path())->run('./docker stats --no-stream')->output();

        $stats = str($output)
            ->explode("\n")
            ->map(fn($line) => str($line)->explode('   ')->filter()->values())
            ->map(fn($line) => [
                'container' => str($line[0] ?? '')->trim()->toString(),
                'name' => str($line[1] ?? '')->trim()->toString(),
                'cpu' => str($line[2] ?? '')->trim()->toString(),
                'mem' => str($line[3] ?? '')->before('/')->trim()->toString(),
                'mem_perc' => str($line[4] ?? '')->trim()->toString(),
                'net_io' => str($line[5] ?? '')->trim()->toString(),
                'block_io' => str($line[6] ?? '')->trim()->toString(),
                'pids' => str($line[7] ?? '')->trim()->toString(),
            ])
            ->collect()
            ->skip(1)
            ->values();

        Cache::put('joe-stats', $stats, 15);
    }
}
