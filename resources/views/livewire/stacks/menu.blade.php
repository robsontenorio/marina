<?php

use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    #[On('stack-updated')]
    public function stacks(): array
    {
        return File::directories(base_path(".data/stacks"));
    }

    public function with(): array
    {
        return [
            'stacks' => $this->stacks()
        ];
    }
}; ?>

<div>
    @foreach($stacks as $stack)
        <x-menu-item title="{{ basename($stack) }}" icon="o-server-stack" link="/stacks/{{ str(basename($stack))->toBase64() }}" />
    @endforeach
</div>
