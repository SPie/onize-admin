<?php

namespace App\Http\Livewire\Users;

use App\Http\Livewire\Component;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class Register extends Component
{
    public const NAME_REGISTER = 'users.register';

//    private ?Factory $viewFactory = null;
//
//    public function mount(Factory $viewFactory): void
//    {
//        $this->viewFactory = $viewFactory;
//    }

    public function render(Factory $viewFactory): View
    {
        return $viewFactory->make('livewire.users.register');
    }
}
