<?php

use App\Actions\Services\FetchServicesAction;
use App\Actions\Services\ForceServiceUpdateAction;
use App\Actions\Services\RemoveServiceAction;
use App\Actions\Services\ScaleDownServicesAction;
use App\Actions\Services\ScaleUpServicesAction;
use App\Actions\Stack\RemoveStackAction;
use App\Actions\Support\CalculateCPUAction;
use App\Actions\Support\CalculateMemoryAction;
use App\Actions\Tasks\FetchTasksAction;
use App\Entities\Service;
use App\Entities\Stats;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

new class extends Component {
    use Toast;

    public ?Collection $services;

    public function with(): array
    {
        $this->services = new FetchServicesAction()->execute();

        return [
            'stats' => new Stats(
                new CalculateCPUAction($this->services->pluck('tasks')->flatten(1))->execute(),
                new CalculateMemoryAction($this->services->pluck('tasks')->flatten(1))->execute()
            )
        ];
    }
}; ?>

<div wire:poll>
    <x-header title="Dashboard" separator />

    <livewire:stats :$stats :$services />
    <livewire:services.index :$services />
</div>
