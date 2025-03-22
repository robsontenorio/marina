<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;

class FetchDockerStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $output = Process::path(base_path())->run('docker stats --no-stream')->output();

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

        Cache::put('marina-stats', $stats, 15);
    }
}
