<?php

use App\Actions\Stack\DeployStackAction;
use App\Actions\Stack\RemoveStackAction;
use App\Actions\Stack\RenameStackAction;
use App\Actions\Stack\SyncEnvsAction;
use App\Actions\Stack\UpdateStackAction;
use App\Actions\Stack\ScanEnvsAction;
use App\Actions\Stack\ScanStackAction;
use App\Actions\Stack\TrashStackAction;
use Illuminate\Support\Collection;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public string $group = '';

    public ?string $stackPreviousName = null;

    public ?Collection $previousEnvs;

    #[Validate('required|regex:/^[\pL\pM\pN_-].+$/u')]
    public string $stack = '';

    #[Validate('required')]
    public string $stackContent = '';

    #[Validate('sometimes')]
    #[Validate(['envs.*.name' => 'required'])]
    #[Validate(['envs.*.content' => 'required'])]
    public ?Collection $envs;

    public function mount(string $stack): void
    {
        $this->stack = $stack;
        $this->stackContent = (new ScanStackAction($stack))->execute();
        $this->envs = (new ScanEnvsAction($stack))->execute();
        $this->previousEnvs = $this->envs;
    }

    public function updatingStack($newValue): void
    {
        $this->stackPreviousName = $this->stack;
    }

    public function deploy(): void
    {
        $this->validate();

        (new UpdateStackAction($this->stack, $this->stackContent, $this->stackPreviousName))->execute();
        (new SyncEnvsAction($this->stack, $this->envs, $this->previousEnvs))->execute();
        (new DeployStackAction($this->stack))->execute();

        $this->group = '';

        $this->success('Running command ...', position: 'toast-bottom', redirectTo: "/stacks/" . str($this->stack)->toBase64 . "/{$this->stack}");
    }

    public function addEnvFile(): void
    {
        $name = '.env' . $this->envs->count();

        $this->envs->add(['name' => $name, 'content' => '']);

        $this->group = $name;
    }

    public function remove(): void
    {
        (new RemoveStackAction($this->stack))->execute();

        $this->success('Running command ...', position: 'toast-bottom', timeout: 5000);
    }

    public function trashStack(): void
    {
        (new TrashStackAction($this->stack))->execute();

        $this->success('Stack trashed', position: 'toast-bottom', timeout: 5000, redirectTo: '/all-stacks');
    }

    public function trashEnv(int $index): void
    {
        $this->envs->forget($index);
    }
}; ?>

<div>
    <x-header :title="$stackPreviousName ?? $stack" separator>
        <x-slot:actions>
            <x-button
                label="Trash"
                wire:click="trashStack"
                wire:confirm="THIS IS A DESTRUCTIVE ACTION!\n\nAre you sure you want to TRASH this `stack` and `env` files?"
                tooltip-left="Hard delete files from disk."
                icon="o-trash"
                spinner responsive
            />

            <x-button
                label="Remove"
                wire:click="remove"
                wire:confirm="Are you sure?"
                tooltip-left="`docker stack rm {{ $stack }}`"
                icon="o-bookmark-slash"
                spinner
                responsive />
        </x-slot:actions>
    </x-header>

    <x-form wire:submit="deploy">
        <x-input placeholder="Stack Name" wire:model="stack" prefix="path/to/stacks/" icon="o-server" />

        <x-accordion wire:model="group" class="mt-5">
            <x-collapse name="compose" class="bg-base-100">
                <x-slot:heading>
                    docker-compose.yml
                </x-slot:heading>
                <x-slot:content class="!mx-5">
                    <x-code-mirror wire:model="stackContent" class="my-5" />
                </x-slot:content>
            </x-collapse>

            @foreach($envs as $env)
                <x-collapse name="{{ $env['name'] }}" class="bg-base-100">
                    <x-slot:heading>
                        {{ $env['name'] }}
                    </x-slot:heading>
                    <x-slot:content class="!mx-5">
                        <x-input label="Filename" wire:model="envs.{{ $loop->index }}.name" inline />
                        <x-code-mirror wire:model="envs.{{ $loop->index }}.content" mode="javascript" class="my-5" />
                        <x-button label="Trash" icon="o-trash" wire:click="trashEnv({{ $loop->index }})" class="btn-ghost text-red-500" />
                    </x-slot:content>
                </x-collapse>
            @endforeach

            {{--  ADD ENV FILE --}}
            <x-button label="Add `.env` file" icon="o-plus" wire:click="addEnvFile" class="join-item border border-base-300" spinner />
        </x-accordion>

        {{-- VALIDATION ERRORS--}}
        <x-errors />

        {{-- FORM ACTIONS --}}
        <x-slot:actions>
            <x-button label="Deploy" type="submit" class="btn-primary" icon="o-fire" spinner="deploy" />
        </x-slot:actions>
    </x-form>
</div>
