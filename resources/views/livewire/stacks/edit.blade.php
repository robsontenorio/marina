<?php

use App\Actions\Stack\DeployStackAction;
use App\Actions\Stack\RemoveStackAction;
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

    public bool $showLogs = false;

    public ?string $group = null;

    public ?string $stackOriginalName = null;

    #[Validate('required|alpha_dash:ascii')]
    public string $stack = '';

    #[Validate('required')]
    public string $stackContent = '';

    #[Validate('sometimes')]
    #[Validate(['envs.*.name' => 'required|distinct:ignore_case|regex:/^\.env(\.[a-zA-Z0-9_-]+)?$/'], attribute: ['envs.*.name' => 'name'])]
    #[Validate(['envs.*.content' => 'required'], attribute: ['envs.*.content' => 'content'])]
    public ?Collection $envs;

    public function mount(string $hash): void
    {
        $this->stack = str($hash)->fromBase64();
        $this->stackOriginalName = $this->stack;
        $this->stackContent = new ScanStackAction($this->stack)->execute();
        $this->envs = new ScanEnvsAction($this->stack)->execute();
    }

    public function remove(): void
    {
        $this->showLogs = true;
        new RemoveStackAction($this->stack)->execute();
    }

    public function trash(): void
    {
        new TrashStackAction($this->stack)->execute();

        $this->success('Stack trashed', position: 'toast-bottom', timeout: 5000, redirectTo: '/');
    }

    public function copy(): void
    {
        $this->success("Copied", position: 'toast-top toast-center');
    }

    public function deploy(): void
    {
        $this->group = $this->group == "docker-compose" ? null : $this->group;
        $this->validate();
        $this->showLogs = true;
        $this->dispatch('stack-updated');

        new UpdateStackAction($this->stack, $this->stackContent, $this->stackOriginalName)->execute();
        new SyncEnvsAction($this->stack, $this->envs)->execute();
        new DeployStackAction($this->stack)->execute();
    }

    public function addEnv(): void
    {
        $this->envs->add(['name' => null, 'content' => null]);
        $this->group = $this->envs->keys()->last();
    }

    public function trashEnv(int $index): void
    {
        $this->envs->forget($index);
        $this->group = null;
    }
}; ?>

<div>
    <x-header :title="$stackOriginalName ?? $stack" separator />

    <x-form wire:submit="deploy">
        <div class="grid grid-cols-2 gap-8">
            {{-- STACK NAME --}}
            <div>
                <x-input label="Name" placeholder="Stack name" wire:model="stack" icon="o-server-stack" />
                <div wire:dirty wire:target="stack" class="text-error text-xs mt-3">Renaming the stack will trigger `stack rm`, and so deploy a new one.</div>
            </div>

            {{--  WEBHOOK --}}
            <div x-data="{ copy() { navigator.clipboard.writeText('{{ url('/deploy/?stack='.Crypt::encryptString($stack)) }}') } }">
                <div class="text-xs font-semibold mb-3 mt-2">Deployment webhook</div>
                <x-button
                    label="Copy secret URL"
                    icon="o-document-duplicate"
                    @click="copy(); $wire.copy()"
                    class="join-item"
                    tooltip="Call this secret endpoint to trigger a deploy"
                    spinner="copy"
                />
            </div>
        </div>

        <div class="text-xs font-semibold mt-5">Definition</div>

        {{-- DOCKER-COMPOSE.YAML --}}
        <x-accordion wire:model="group">
            <x-collapse name="docker-compose" class="bg-base-100" separator>
                <x-slot:heading @class(["text-error" => $errors->has('stackContent')])>
                    <x-icon :name="$errors->has('stackContent') ? 's-exclamation-triangle' : 'o-queue-list'" label="docker-compose.yml" />
                </x-slot:heading>
                <x-slot:content>
                    <x-ace wire:model="stackContent" language="yaml" height="600px" hint="A valid docker compose syntax for Swarm." />
                </x-slot:content>
            </x-collapse>

            {{-- ENV FILES --}}
            @foreach($envs as $k => $env)
                <x-collapse :name="$k" class="bg-base-100" separator>
                    <x-slot:heading @class(["text-error" => $errors->has('envs.'.$k.'.*')])>
                        <x-icon :name="$errors->hasAny('envs.*') ? 's-exclamation-triangle' : 'o-document'" :label="$env['name'] ? $env['name'] : '<no name>'" />
                    </x-slot:heading>
                    <x-slot:content class="px-20">
                        <x-input label="Name" wire:model.live="envs.{{ $k }}.name" hint="Ex: .env.app1" class="max-w-96" />
                        <x-ace label="Content" wire:model="envs.{{ $k }}.content" language="ini" hint="A valid dotenv syntax." />
                        <x-button label="Trash" icon="o-trash" wire:confirm="Are you sure?" wire:click="trashEnv({{ $k }})" class="btn-sm text-error my-5" />
                    </x-slot:content>
                </x-collapse>
            @endforeach

            {{--  ADD ENV FILE --}}
            <x-button label="Add `.env`" icon="o-plus" wire:click="addEnv" class="join-item btn-soft border-base-300" spinner />
        </x-accordion>

        @if($errors->any())
            <x-icon name="o-exclamation-triangle" label="There are some validation errors" class="text-error text-sm w-4 h-4" />
        @endif

        {{-- NOTES --}}
        <div class="fieldset-label mt-5 grid text-xs">
            <x-icon name="o-light-bulb" label="Make sure to add the respective `env` file for the service, when needed." class="w-4 h-4" />
            <x-icon name="o-light-bulb" label="If you are using private images, make sure to add a credential." class="w-4 h-4" />
        </div>

        {{-- FORM ACTIONS --}}
        <x-slot:actions>
            <x-button
                label="Trash"
                wire:click="trash"
                wire:confirm="This will stop the containers and DELETE the configuration files from this stack.\n\nAny volumes or networks created by this stack will be kept."
                tooltip="Stop and delete the stack."
                icon="o-trash"
                class="text-error btn-ghost"
                spinner
                responsive />

            <x-button
                label="Remove"
                wire:click="remove"
                wire:confirm="This only stops the containers from this stack.\n\n Any volumes or networks created by this stack will be kept."
                tooltip="`docker stack rm {{ $stack }}`"
                icon="o-bookmark-slash"
                spinner
                responsive
            />

            <x-button label="Deploy" type="submit" class="btn-primary" icon="o-fire" spinner="deploy" responsive />
        </x-slot:actions>
    </x-form>

    {{--  LOGS MODAL --}}
    <livewire:logs wire:model="showLogs" action-label="Go to stack" action-link="/stacks/{{ str($stack)->toBase64() }}" @close="$toggle('showLogs')" :key="uniqid()" />
</div>
