<?php

use App\Actions\Services\ForceServiceUpdateAction;
use App\Actions\Services\RemoveServiceAction;
use App\Actions\Services\ScaleDownServicesAction;
use App\Actions\Services\ScaleUpServicesAction;
use App\Entities\Service;
use Illuminate\Support\Collection;
use Livewire\Attributes\Reactive;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    #[Reactive]
    public Service $service;

    // Scale service up
    public function scaleUp(string $id): void
    {
        (new ScaleUpServicesAction($this->service))->execute();

        $this->success('Running command ...', position: 'toast-bottom', timeout: 5000);
    }

    // Scale service down
    public function scaleDown(string $id): void
    {
        (new ScaleDownServicesAction($this->service))->execute();

        $this->success('Running command ...', position: 'toast-bottom', timeout: 5000);
    }

    // Force update service
    public function forceUpdate(string $id): void
    {
        (new ForceServiceUpdateAction($this->service))->execute();

        $this->success('Running command ...', position: 'toast-bottom', timeout: 5000);
    }

    // Remove service
    public function removeService(string $id): void
    {
        (new RemoveServiceAction($this->service))->execute();

        $this->success('Running command ...', position: 'toast-bottom', timeout: 5000);
    }
}; ?>

<div>
    <div class="flex justify-between">
        <div class="flex-1">
            <div class="flex gap-3">
                {{--  REPLICAS--}}
                <div class="flex gap-3">
                    <div @class(["bg-base-300 text-base-content rounded-lg text-center py-2 px-3", "!bg-success !text-base-100"  => $service->isRunning()])>
                        <div class="font-black">{{ $service->replicas }}</div>
                        <div class="text-xs">replicas</div>
                    </div>
                    <div class="grid">
                        <x-button
                            tooltip="Scale Up"
                            wire:click.stop="scaleUp('{{ $service->id }}')"
                            class="btn-ghost btn-sm btn-circle"
                            icon="o-chevron-up"
                            spinner />

                        <x-button
                            tooltip="Scale Down"
                            wire:click.stop="scaleDown('{{ $service->id }}')"
                            :disabled="$service->replicas == 0"
                            icon="o-chevron-down"
                            class="btn-ghost btn-sm btn-circle"
                            spinner />
                    </div>
                </div>
                <div>
                    {{--  SERVICE --}}
                    <div class="font-black text-xl mb-3">
                        {{ $service->name }}
                        <span data-tip="This service is updating" @class(["hidden", "tooltip !inline-block" => $service->isUpdating()]) >
                            <x-loading class="loading-ring -mb-2" />
                        </span>
                    </div>

                    {{--  STATS--}}
                    <div>
                        <span class="tooltip" data-tip="cpu / mem">
                            <x-icon name="o-cpu-chip" label="{{ $service->stats->cpu ?? '-' }} / {{ $service->stats->memory ?? '-' }}" class="text-xs" />
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <x-button
                tooltip-left="`docker service rm {service}`"
                wire:click.stop="removeService('{{ $service->id }}')"
                wire:confirm="Are you sure?"
                icon="o-bookmark-slash"
                class="btn-ghost btn-sm btn-circle"
                spinner />

            <x-button
                tooltip-left="`docker service update --force {service}`"
                wire:click.stop="forceUpdate('{{ $service->id }}')"
                wire:confirm="Are you sure?"
                icon="o-fire"
                class="btn-ghost btn-sm btn-circle"
                spinner />
        </div>
    </div>
</div>
