<?php

use App\Actions\Stack\DeployStackAction;
use App\Actions\Stack\SaveEnvAction;
use App\Actions\Stack\SaveStackAction;
use App\Actions\Stack\ScanEnvsAction;
use App\Actions\Stack\ScanStackAction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public string $stack = '';

    public string $group = '';

    public string $stackContent = '';

    public ?Collection $envs;

    public function mount(string $stack): void
    {
        $this->stack = $stack;
        $this->stackContent = (new ScanStackAction($stack))->execute();
        $this->envs = (new ScanEnvsAction($stack))->execute();
    }

    public function saveStack(): void
    {
        (new SaveStackAction($this->stack, $this->stackContent))->execute();

        $this->success('Saved.', position: 'toast-bottom');
    }

    public function saveAndDeploy(): void
    {
        (new SaveStackAction($this->stack, $this->stackContent))->execute();
        (new DeployStackAction($this->stack))->execute();

        $this->success('Stack deploy command is running ...', position: 'toast-bottom', redirectTo: "/stacks/{$this->stack}");
    }

    public function deploy(): void
    {
        (new DeployStackAction($this->stack))->execute();
    }

    public function saveEnv(string $fileName): void
    {
        $content = $this->envs->firstWhere('name', $fileName)['content'];

        (new SaveEnvAction($this->stack, $fileName, $content))->execute();

        $this->success('Saved.', position: 'toast-bottom');
    }
}; ?>

<div>
    <x-header :title="$stack" separator />

    <x-accordion wire:model="group">

        <x-collapse name="compose" class="bg-base-100">
            <x-slot:heading>
                docker-compose.yml
            </x-slot:heading>
            <x-slot:content>
                <x-form wire:submit="saveStack">
                    <x-textarea wire:model="stackContent" rows="20" />
                    <x-slot:actions>
                        <x-button label="Save & Deploy" wire:click="saveAndDeploy" class="btn-neutral" icon="o-fire" spinner />
                        <x-button label="Save" type="submit" class="btn-primary" icon="o-paper-airplane" spinner="saveStack" />
                    </x-slot:actions>
                </x-form>
            </x-slot:content>
        </x-collapse>

        @foreach($envs as $env)
            <x-collapse name="{{ $env['name'] }}" class="bg-base-100">
                <x-slot:heading>
                    {{ $env['name'] }}
                </x-slot:heading>
                <x-slot:content>
                    <x-form wire:submit.prevent="saveEnv('{{ $env['name'] }}')">
                        <x-textarea wire:model="envs.{{ $loop->index }}.content" rows="10" />
                        <x-slot:actions>
                            <x-button label="Save" type="submit" class="btn-primary" icon="o-paper-airplane" spinner="saveEnv('{{ $env['name'] }}')" />
                        </x-slot:actions>
                    </x-form>
                </x-slot:content>
            </x-collapse>
        @endforeach
    </x-accordion>
</div>
