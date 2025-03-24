<?php

use App\Actions\Crendentials\GetCredentialsAction;
use App\Actions\Crendentials\LoginAction;
use App\Actions\Crendentials\LogoutAction;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public bool $show = false;

    #[Validate('required|url')]
    public ?string $url = null;

    #[Validate('required')]
    public ?string $username = null;

    #[Validate('required')]
    public ?string $access_token = null;

    public function add(): void
    {
        $data = $this->validate();

        $action = new LoginAction($data)->execute();

        $this->reset();
        $this->success('Credential added.');
    }

    public function remove(string $url): void
    {
        new LogoutAction($url)->execute();

        $this->success('Credential removed');
    }

    public function with(): array
    {
        return [
            'registries' => new GetCredentialsAction()->execute()
        ];
    }
}; ?>

<div>
    <x-header title="Credentials" separator progress-indicator>
        <x-slot:actions>
            <x-button label="Add" icon="o-plus" class="btn-primary" @click="$wire.show = true" responsive />
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-3 gap-5 w-full">
        @foreach($registries as $registry)
            <x-card shadow>
                <x-list-item :item="$registry" value="domain" sub-value="username" no-separator no-hover>
                    <x-slot:avatar>
                        <x-icon :name="$registry->icon" class="w-9 h-9" />
                    </x-slot:avatar>
                    <x-slot:actions>
                        <x-button
                            icon="o-trash"
                            class="text-error btn-sm btn-circle btn-ghost"
                            wire:confirm="Remove this registry credential?"
                            wire:click="remove('{{  $registry->url }}')"
                            spinner="remove('{{  $registry->url }}')"
                        />
                    </x-slot:actions>
                </x-list-item>
            </x-card>
        @endforeach
    </div>

    @if($registries->isEmpty())
        {{-- TODO: create a new component `x-????` --}}
        <div class="text-center">
            <x-icon name="o-squares-2x2" class="w-10 h-10 bg-warning/50 p-2 rounded-full" />
            <div class="text-xl pl-3 mt-3">No credentials</div>
            <div class="text-sm text-gray-500 mt-3">If you are using private images, make sure to register the credentials here.</div>
        </div>
    @else
        <div class="fieldset-label text-sm mt-10">
            <x-icon name="o-light-bulb" label="After adding a new credential, make sure to re-deploy the respective stack." class="w-4 h-4" />
        </div>
    @endif

    <x-modal wire:model="show" title="Credential" separator>
        <x-form wire:submit="add">
            <x-input label="Registry URL" icon="o-globe-alt" wire:model="url" />
            <x-input label="Username" icon="o-user" wire:model="username" />
            <x-password label="Personal Access Token (not your password)" wire:model="access_token" hint="Make sure this token has permission to read images." />

            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.show = false" />
                <x-button label="Add" type="submit" class="btn-primary" spinner="add" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
