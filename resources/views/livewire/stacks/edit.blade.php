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
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public bool $showLogs = false;

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
        new DeployStackAction($this->stack, $this, 'logs')->execute();

        $this->showLogs = true;
        $this->group = '';
    }

    public function addEnvFile(): void
    {
        $name = '.env' . $this->envs->count();

        $this->envs->add(['name' => $name, 'content' => '']);

        $this->group = $name;
    }

    public function remove(): void
    {
        $this->showLogs = true;
        new RemoveStackAction($this->stack, $this, 'logs')->execute();
    }

    public function trashStack(): void
    {
        (new TrashStackAction($this->stack))->execute();

        $this->success('Stack trashed', position: 'toast-bottom', timeout: 5000, redirectTo: '/');
    }

    public function copy(): void
    {
        $this->success("Copied", position: 'toast-top toast-center');
    }

    public function trashEnv(int $index): void
    {
        $this->envs->forget($index);
    }
}; ?>

<div>
    <x-header :title="$stackPreviousName ?? $stack" separator />

    <x-form wire:submit="deploy">
        <div class="grid grid-cols-2 gap-8">
            {{-- STACK NAME --}}
            <x-input label="Name" placeholder="Stack Name" wire:model="stack" icon="o-server-stack" />

            {{--  WEBHOOK --}}
            <div x-data="{ copied: false, copy() { const i = this.$refs.input; i.select(); navigator.clipboard.writeText(i.value) } }">
                <x-input x-ref="input" label="Deploy webhook" type="password" value="{{ url('id?='.Crypt::encryptString($stack)) }}" readonly>
                    <x-slot:append>
                        <x-button
                            icon="o-document-duplicate"
                            @click="copy(); $wire.copy()"
                            class="join-item"
                            tooltip-left="Call this secret endpoint to trigger a deploy"
                        />
                    </x-slot:append>
                </x-input>
            </div>
        </div>

        <fieldset class="fieldset">
            <legend class="fieldset-legend mb-0.5">Stack definition</legend>

            {{-- DOCKER-COMPOSE.YAML --}}
            <x-accordion wire:model="group" class="text-base">
                <x-collapse name="compose" class="bg-base-100 text-base">
                    <x-slot:heading>
                        docker-compose.yml
                    </x-slot:heading>
                    <x-slot:content class="!mx-5">
                        <x-code-mirror wire:model="stackContent" class="my-5" />
                    </x-slot:content>
                </x-collapse>

                {{-- ENV FILES --}}
                @foreach($envs as $env)
                    <x-collapse name="{{ $env['name'] }}" class="bg-base-100">
                        <x-slot:heading>
                            {{ $env['name'] }}
                        </x-slot:heading>
                        <x-slot:content class="!mx-5">
                            <x-input label="Filename" wire:model="envs.{{ $loop->index }}.name" inline />
                            <x-code-mirror wire:model="envs.{{ $loop->index }}.content" mode="javascript" class="my-5" />
                            <x-button label="Trash" icon="o-trash" wire:click="trashEnv({{ $loop->index }})" class="btn-ghost text-error" />
                        </x-slot:content>
                    </x-collapse>
                @endforeach

                {{--  ADD ENV FILE --}}
                <x-button label="Add `.env` file" icon="o-plus" wire:click="addEnvFile" class="join-item" spinner />
            </x-accordion>

            {{-- NOTES --}}
            <div class="fieldset-label mt-5 grid">
                <x-icon name="o-light-bulb" label="Make sure to add the respective `env` file for the service, when needed." class="w-4 h-4" />
                <x-icon name="o-light-bulb" label="If you are using a private images, make sure to add a credential." class="w-4 h-4" />
            </div>
        </fieldset>

        {{-- VALIDATION ERRORS--}}
        <x-errors />

        {{-- FORM ACTIONS --}}
        <x-slot:actions>
            <x-button
                label="Trash"
                wire:click="trashStack"
                wire:confirm="THIS IS A DESTRUCTIVE ACTION!\n\nAre you sure you want to TRASH this `stack` and `env` files?"
                tooltip-left="Hard delete files from disk."
                icon="o-trash"
                class="text-error btn-ghost"
                spinner
                responsive />

            <x-button
                label="Remove"
                wire:click="remove"
                wire:confirm="Are you sure?"
                tooltip-left="`docker stack rm {{ $stack }}`"
                icon="o-bookmark-slash"
                spinner
                responsive
            />

            <x-button
                label="Deploy"
                type="submit"
                class="btn-primary"
                icon="o-fire"
                spinner="deploy"
                wire:click="deploy"
                responsive
            />
        </x-slot:actions>
    </x-form>

    {{--  LOGS MODAL --}}
    <livewire:logs wire:model="showLogs" @close="$toggle('showLogs')" />
</div>
