<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
#[Layout('components.layouts.empty')]
#[Title('Registration')]
class extends Component {
    #[Rule('required')]
    public string $name = '';

    #[Rule('required|email|unique:users')]
    public string $email = '';

    #[Rule('required|confirmed')]
    public string $password = '';

    #[Rule('required')]
    public string $password_confirmation = '';

    public function mount()
    {
        if (auth()->user() || User::count()) {
            return redirect('/');
        }
    }

    public function register()
    {
        $data = $this->validate();

        $data['avatar'] = "https://gravatar.com/avatar/" . hash('sha256', $data['email']);
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        auth()->login($user);

        request()->session()->regenerate();

        return redirect('/');
    }
}; ?>

<div class="md:w-96 mx-auto mt-20">
    <div class="mb-5">
        <img src="/images/marina.png" class="h-12" />
    </div>

    <x-form wire:submit="register">
        <x-input label="Name" wire:model="name" icon="o-user" />
        <x-input label="E-mail" wire:model="email" icon="o-envelope" />
        <x-input label="Password" wire:model="password" type="password" icon="o-key" />
        <x-input label="Confirm Password" wire:model="password_confirmation" type="password" icon="o-key" />

        <x-slot:actions>
            <x-button label="Register" type="submit" icon="o-paper-airplane" class="btn-primary" spinner="register" />
        </x-slot:actions>
    </x-form>
</div>
