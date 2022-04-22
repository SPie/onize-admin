<?php

namespace App\Http\Livewire\Users;

use App\Auth\AuthManager;
use App\Http\Livewire\Component;
use App\Users\UserManager;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Livewire\Redirector;

class Register extends Component
{
    public const NAME_REGISTER = 'users.register';

    public string $email = '';

    public string $password = '';

    public string $passwordConfirm = '';

    private ?UserManager $userManager = null;

    private ?AuthManager $authManager = null;

    private ?Redirector $redirector = null;

    public function mount(UserManager $userManager, AuthManager $authManager, Redirector $redirector): void
    {
        $this->userManager = $userManager;
        $this->authManager = $authManager;
        $this->redirector = $redirector;
    }

    public function render(Factory $viewFactory): View
    {
        return $viewFactory->make('livewire.users.register');
    }

    public function register(): RedirectResponse
    {
        $this->validate([
            'email'           => ['required', 'email'],
            'password'        => ['required', 'min:12'],
            'passwordConfirm' => ['required', 'same:password'],
        ]);

        $user = $this->userManager->register($this->email, $this->password);

        $this->authManager->loginUser($user);

        return $this->redirector->route('home');
    }
}
