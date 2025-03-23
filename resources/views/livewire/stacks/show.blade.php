<?php

use App\Actions\Services\FetchServicesAction;
use App\Actions\Services\ForceServiceUpdateAction;
use App\Actions\Services\RemoveServiceAction;
use App\Actions\Services\ScaleDownServicesAction;
use App\Actions\Services\ScaleUpServicesAction;
use App\Actions\Stack\DeployStackAction;
use App\Actions\Stack\RemoveStackAction;
use App\Actions\Stack\TrashStackAction;
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

    public string $stack = '';

    public ?Collection $services;

    public function mount(string $stack): void
    {
        $this->stack = $stack;
    }

    public function boot(): void
    {
        $this->services = (new FetchServicesAction($this->stack))->execute();
    }

    public function with(): array
    {
        return [
            'stats' => new Stats(
                (new CalculateCPUAction($this->services->pluck('tasks')->flatten(1)))->execute(),
                (new CalculateMemoryAction($this->services->pluck('tasks')->flatten(1)))->execute()
            )
        ];
    }
}; ?>

<div wire:poll>
    <x-header :title="$stack" separator>
        <x-slot:actions>
            <x-button label="Settings" icon="o-cog-6-tooth" link="/stacks/{{ str($stack)->toBase64 }}/{{ $stack }}/edit" responsive />
        </x-slot:actions>
    </x-header>

    <livewire:stats :$stats :$services />
    <livewire:services.index :$services />

    <div class="fieldset-label text-sm mt-10">
        <x-icon name="o-light-bulb" class="w-4 h-4" />
        If you are using a private images, make sure to <a href="/credentials" wire:navigate class="!underline">add a credential.</a>
    </div>
</div>
