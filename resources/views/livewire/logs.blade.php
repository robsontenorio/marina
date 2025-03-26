<?php

use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Volt\Component;

new class extends Component {
    #[Modelable]
    public bool $show = false;

    public ?string $actionLink = null;

    public ?string $actionLabel = null;
}; ?>

<div>
    <div data-theme="dark">
        <x-modal wire:model="show" box-class="max-w-screen-lg rounded text-sm" persistent>
            <div class="flex justify-between sticky -top-7 z-10 -m-6 px-6 py-3 bg-base-100 border-b border-b-base-content/10">
                <h2 class="text-lg font-bold">Logs</h2>

                <div class="flex gap-3">
                    <x-button label="Close" wire:click="$dispatch('close')" class="btn-sm btn-outline" />
                    @if($actionLink)
                        <x-button :label="$actionLabel" :link="$actionLink" icon-right="o-arrow-right" class="btn-outline btn-sm" />
                    @endif
                </div>
            </div>

            <div x-data x-init="$watch('$wire.show', () => { try { $refs.logs.innerHTML = '' } catch {} })" class="min-h-96 pt-10">
                <template x-if="$wire.show">
                    <div x-ref="logs" wire:poll>{!!  cache()->get('logs') !!}</div>
                </template>

                <div x-show="$refs?.logs?.innerHTML" class="loading loading-dots"></div>
            </div>
        </x-modal>
    </div>
</div>
