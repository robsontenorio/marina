<?php

namespace App\Traits;

use Illuminate\Support\Facades\Process;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

trait RunsLoggableCommand
{
    private function run(string $command): void
    {
        cache()->forget('logs');

        $this->info($command);

        Process::path(base_path())->run($command, function (string $type, string $output) {
            $this->log($type, $output);
        });
    }

    private function log(string $type, string $output): void
    {
        $output = $type == 'err' ? "<span class='text-error'>ERROR: {$output}</span>" : $output;
        $log = str($output)->stripTags() != $output ? $output : new AnsiToHtmlConverter()->convert($output);
        $log = nl2br($log);
        $logs = cache()->get('logs', '') . $log;
        cache()->put('logs', $logs);
    }

    private function info(string $text): void
    {
        $this->log('out', "<span class='text-info'>✨ {$text}</span>\n\n");
    }
}
