<?php

namespace App\Http\Livewire\Auth;

use App\Auth\AuthManager;
use App\Http\Livewire\Component;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class Login extends Component
{
    public const NAME_LOGIN = 'auth.login';

    public string $email = '';

    public string $password = '';

    public function render(Factory $viewFactory): View
    {
        return $viewFactory->make('livewire.auth.login')->layout('layouts.signup');
    }

    public function login(AuthManager $authManager): void
    {
        $this->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        try {
            $authManager->login($this->email, $this->password);

            $this->redirectRoute('home');
        } catch (AuthenticationException $e) {
            $this->addError('email', 'validation.invalid-credentials');
        }
    }
}
