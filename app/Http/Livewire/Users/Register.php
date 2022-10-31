<?php

namespace App\Http\Livewire\Users;

use App\Auth\AuthManager;
use App\Http\Livewire\Component;
use App\Users\UserManager;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

final class Register extends Component
{
    public const NAME_REGISTER = 'users.register';

    public string $email = '';

    public string $password = '';

    public string $passwordConfirm = '';

    public function render(Factory $viewFactory): View
    {
        return $viewFactory->make('livewire.users.register')->layout('layouts.signup');
    }

    public function register(UserManager $userManager, AuthManager $authManager): void
    {
        $this->validate([
            'email'           => ['required', 'email'],
            'password'        => ['required', 'min:12'],
            'passwordConfirm' => ['required', 'same:password'],
        ]);

        $user = $userManager->register($this->email, $this->password);

        $authManager->loginUser($user);

        $this->redirectRoute('home');
    }
}
