<?php

use App\Actions\Services\FetchServicesAction;
use App\Actions\Support\CalculateCPUAction;
use App\Actions\Support\CalculateMemoryAction;
use App\Entities\Stats;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public ?string $stack = null;

    public ?Collection $services;

    public function mount(string $hash): void
    {
        $this->stack = str($hash)->fromBase64();
    }

    public function with(): array
    {
        $this->services = new FetchServicesAction($this->stack)->execute();

        return [
            'stats' => new Stats(
                new CalculateCPUAction($this->services->pluck('tasks')->flatten(1))->execute(),
                new CalculateMemoryAction($this->services->pluck('tasks')->flatten(1))->execute()
            )
        ];
    }
}; ?>

<div wire:poll>
    <x-header :title="$stack" separator>
        <x-slot:actions>
            <x-button label="Settings" icon="o-cog-6-tooth" link="/stacks/{{ str($stack)->toBase64() }}/edit" responsive />
        </x-slot:actions>
    </x-header>

    <livewire:stats :$stats :$services />
    <livewire:services.index :$services />

    @if($services->count())
        <div class="fieldset-label text-sm mt-10">
            <x-icon name="o-light-bulb" class="w-4 h-4" />
            If you are using private images, make sure to <a href="/credentials" wire:navigate class="!underline">add a credential.</a>
        </div>
    @endif
</div>
