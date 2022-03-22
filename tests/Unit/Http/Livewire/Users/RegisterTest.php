<?php

namespace Tests\Unit\Http\Livewire\Users;

use App\Http\Livewire\Users\Register;
use Mockery as m;
use Mockery\MockInterface;
use Tests\Helpers\LaravelHelpers;
use Tests\TestCase;

final class RegisterTest extends TestCase
{
    use LaravelHelpers;

    /**
     * @return Register|MockInterface
     */
    private function getRegister(): Register
    {
        return m::mock(Register::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
    }

    public function testRender(): void
    {
        $view = $this->createView();
        $viewFactory = $this->createViewFactory();
        $this->mockViewFactoryMake($viewFactory, $view, 'livewire.users.register');

        $this->assertEquals($view, $this->getRegister()->render($viewFactory));
    }
}
