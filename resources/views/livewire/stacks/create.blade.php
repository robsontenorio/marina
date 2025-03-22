<?php

use App\Actions\Stack\CreateStackAction;
use App\Actions\Stack\DeployStackAction;
use App\Actions\Stack\SaveEnvAction;
use App\Actions\Stack\TrashEnvAction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public string $group = '';

    #[Validate('required|regex:/^[\pL\pM\pN_-].+$/u')]
    public string $stack = '';

    #[Validate('required')]
    public string $stackContent = '';

    #[Validate('sometimes')]
    #[Validate(['envs.*.name' => 'required'])]
    #[Validate(['envs.*.content' => 'required'])]
    public ?Collection $envs;

    public function mount(): void
    {
        $this->envs = new Collection([['name' => '.env', 'content' => '']]);
    }

    public function deploy(): void
    {
        $this->validate();

        (new CreateStackAction($this->stack, $this->stackContent))->execute();

        $this->envs->each(function ($env) {
            (new SaveEnvAction($this->stack, $env['name'], $env['content']))->execute();
        });

        (new DeployStackAction($this->stack))->execute();

        $this->success('Running command ...', position: 'toast-bottom', redirectTo: "/stacks/" . str($this->stack)->toBase64 . "/{$this->stack}");
    }

    public function addEnvFile(): void
    {
        $name = '.env' . $this->envs->count();

        $this->envs->add(['name' => $name, 'content' => '']);

        $this->group = $name;
    }

    public function trashEnv(int $index): void
    {
        $this->envs->forget($index);
    }
}; ?>

<div>
    <x-header title="Add Stack" separator />

    <x-form wire:submit="deploy">
        <x-input placeholder="Stack Name" wire:model="stack" prefix="/path/to/stacks/" icon="o-server" inline />

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
                        {{ $env['name'] ?  $env['name'] : 'env file' }}
                    </x-slot:heading>
                    <x-slot:content class="!mx-5">
                        <x-input label="Filename" wire:model="envs.{{ $loop->index }}.name" inline />
                        <x-code-mirror wire:model="envs.{{ $loop->index }}.content" mode="javascript" class="my-5" />
                        <x-button label="Trash" icon="o-trash" wire:click="trashEnv({{ $loop->index }})" class="btn-ghost text-error" />
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
