<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public string $stack = '';

    public string $group = '';

    public array $envs = [];

    public string $dockerComposeFile = '';

    public function mount(string $stack): void
    {
        $this->stack = $stack;
        $this->scanCompose();
        $this->scanEnvs();
    }

    public function scanEnvs(): void
    {
        $envs = File::glob(base_path("stacks/{$this->stack}/.env*"), true);

        collect($envs)->each(function ($env) {
            $this->envs[] = [
                'name' => basename($env),
                'content' => File::get($env)
            ];
        });
    }

    public function scanCompose(): void
    {
        $this->dockerComposeFile = File::get(base_path("stacks/{$this->stack}/docker-compose.yml"));
    }

    public function saveDockerCompose(): void
    {
        File::put(base_path("stacks/{$this->stack}/docker-compose.yml"), $this->dockerComposeFile);

        $this->success('Saved.', position: 'toast-bottom');
    }

    public function saveAndDeploy(): void
    {
        $this->saveDockerCompose();
        $this->deploy();

        $this->success('Stack deploy command is running ...', position: 'toast-bottom', redirectTo: "/stacks/{$this->stack}");
    }

    public function deploy(): void
    {
        Process::path(base_path())->quietly()->start("./docker stack deploy -c stacks/{$this->stack}/docker-compose.yml {$this->stack} --with-registry-auth");
    }

    public function saveEnv(string $fileName): void
    {
        $index = collect($this->envs)->search(fn($env) => $env['name'] === $fileName);
        File::put(base_path("stacks/{$this->stack}/{$fileName}"), $this->envs[$index]['content']);

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
                <x-form wire:submit="saveDockerCompose">
                    <x-textarea wire:model="dockerComposeFile" rows="20" />
                    <x-slot:actions>
                        <x-button label="Save & Deploy" wire:click="saveAndDeploy" class="btn-neutral" icon="o-fire" spinner />
                        <x-button label="Save" type="submit" class="btn-primary" icon="o-paper-airplane" spinner="saveDockerCompose" />
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
