<?php /** @noinspection ALL */

namespace Tests\Helpers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Session\Session;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
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

    private function mockViewLayout(MockInterface $view, string $layout): CompositeExpectation
    {
        return $view
            ->shouldReceive('layout')
            ->with($layout)
            ->andReturn($view);
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
     * @return UrlGenerator|MockInterface
     */
    private function createUrlGenerator(): UrlGenerator
    {
        return m::spy(UrlGenerator::class);
    }

    private function mockUrlGeneratorRoute(MockInterface $urlGenerator, string $url, string $routeName): CompositeExpectation
    {
        return $urlGenerator
            ->shouldReceive('route')
            ->with($routeName)
            ->andReturn($url);
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

    private function mockSessionGet(MockInterface $session, $item, string $key): CompositeExpectation
    {
        return $session
            ->shouldReceive('get')
            ->with($key)
            ->andReturn($item);
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

    private function mockStatefulGuardAttempt(MockInterface $statefulGuard, bool $valid, array $credentials): CompositeExpectation
    {
        return $statefulGuard
            ->shouldReceive('attempt')
            ->with($credentials)
            ->andReturn($valid);
    }

    private function mockStatefulGuardUser(MockInterface $statefulGuard, ?Authenticatable $user): CompositeExpectation
    {
        return $statefulGuard
            ->shouldReceive('user')
            ->andReturn($user);
    }

    /**
     * @return AuthFactory|MockInterface
     */
    private function createAuthFactory(): AuthFactory
    {
        return m::spy(AuthFactory::class);
    }

    /**
     * @return Request|MockInterface
     */
    private function createRequest(): Request
    {
        return m::spy(Request::class);
    }
}
