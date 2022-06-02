<?php

namespace App\Http\Livewire\Users;

use App\Auth\AuthManager;
use App\Http\Livewire\Component;
use App\Users\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

final class Profile extends Component
{
    public const NAME_PROFILE = 'users.profile';

    private ?User $user = null;

    public string $email = '';

    public string $password = '';

    public string $passwordConfirm = '';

    public function mount(AuthManager $authManager): void
    {
        $this->user = $authManager->authenticatedUser();

        $this->email = $this->user->getEmail();
    }

    public function render(Factory $viewFactory): View
    {
        return $viewFactory->make('livewire.users.profile');
    }
}