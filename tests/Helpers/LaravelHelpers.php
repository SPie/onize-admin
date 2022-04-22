<?php /** @noinspection ALL */

namespace Tests\Helpers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Session\Session;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Livewire\Redirector as LaravelRedirector;
use Mockery as m;
use Mockery\VerificationDirector;
use Mockery\MockInterface;
use Mockery\CompositeExpectation;

trait LaravelHelpers
{
    private function createViewFactory(): ViewFactory&MockInterface
    {
        return m::spy(ViewFactory::class);
    }

    private function mockViewFactoryMake(
        ViewFactory&MockInterface $viewFactory,
        View $view,
        string $viewName,
        array $data = null
    ): MockInterface&ViewFactory {
        $arguments = [$viewName];
        if ($data !== null) {
            $arguments[] = $data;
        }

        return $viewFactory
            ->shouldReceive('make')
            ->withArgs($arguments)
            ->andReturn($view)
            ->getMock();
    }

    /**
     * @return View|MockInterface
     */
    private function createView(): View
    {
        return m::spy(View::class);
    }

    /**
     * @return LaravelRedirector|MockInterface
     */
    private function createLivewireRedirector(): LaravelRedirector
    {
        return m::spy(LaravelRedirector::class);
    }

    private function mockLivewireRedirectorRoute(
        MockInterface $redirector,
        RedirectResponse $redirectResponse,
        string $routeName
    ): CompositeExpectation {
        return $redirector
            ->shouldReceive('route')
            ->with($routeName)
            ->andReturn($redirectResponse);
    }

    /**
     * @return RedirectResponse|MockInterface
     */
    private function createRedirectResponse(): RedirectResponse
    {
        return m::spy(RedirectResponse::class);
    }

    /**
     * @return Session|MockInterface
     */
    private function createSession(): Session
    {
        return m::spy(Session::class);
    }

    private function assertSessionPut(MockInterface $session, array $data): VerificationDirector
    {
        return $session
            ->shouldHaveReceived('put')
            ->with($data)
            ->once();
    }

    /**
     * @return StatefulGuard|MockInterface
     */
    private function createStatefulGuard(): StatefulGuard
    {
        return m::spy(StatefulGuard::class);
    }

    private function assertStatefulGuardLogin(MockInterface $statefulGuard, Authenticatable $user): VerificationDirector
    {
        return $statefulGuard
            ->shouldHaveReceived('login')
            ->with($user)
            ->once();
    }
}
