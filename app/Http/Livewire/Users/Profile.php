<?php

namespace App\Http\Livewire\Users;

use App\Auth\AuthManager;
use App\Http\Livewire\Component;
use App\Users\UserManager;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

final class Profile extends Component
{
    public const NAME_PROFILE = 'users.profile';

    public string $email = '';

    public string $currentPassword = '';

    public string $newPassword = '';

    public string $passwordConfirm = '';

    public bool $editEmail = false;

    public bool $editPassword = false;

    public function mount(AuthManager $authManager): void
    {
        $this->email = $authManager->authenticatedUser()->getEmail();
    }

    public function render(Factory $viewFactory): View
    {
        return $viewFactory->make('livewire.users.profile');
    }

    public function editEmail(UserManager $userManager): void
    {
        $this->validate(['email' => ['required', 'email']]);

        $this->email = $userManager->editProfile($this->email)->getEmail();
        $this->editEmail = false;
    }

    public function editPassword(UserManager $userManager): void
    {
        $this->validate([
            'currentPassword' => ['required'],
            'newPassword'     => ['required'],
            'passwordConfirm' => ['required', 'same:newPassword'],
        ]);

        $userManager->editPassword($this->currentPassword, $this->newPassword);
        $this->editPassword = false;
    }
}