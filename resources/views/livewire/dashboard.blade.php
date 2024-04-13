<?php

use Livewire\Volt\Component;

new class extends Component {
    public string $code = 'codigo';
}; ?>

<div>
    @dump($code)
    <x-code-mirror wire:model.live="code" />
</div>
