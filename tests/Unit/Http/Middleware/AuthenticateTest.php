<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\Authenticate;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Routing\Redirector;
use Tests\Helpers\LaravelHelpers;
use Tests\Helpers\ReflectionHelper;
use Tests\TestCase;

final class AuthenticateTest extends TestCase
{
    use LaravelHelpers;
    use ReflectionHelper;

    private function getAuthenticate(Factory $authFactory = null, UrlGenerator $urlGenerator = null): Authenticate
    {
        return new Authenticate(
            $authFactory ?: $this->createAuthFactory(),
            $urlGenerator ?: $this->createUrlGenerator()
        );
    }

    public function testRedirectTo(): void
    {
        $url = $this->getFaker()->url;
        $urlGenerator = $this->createUrlGenerator();
        $this->mockUrlGeneratorRoute($urlGenerator, $url, 'auth.login');
        $middleware = $this->getAuthenticate(null, $urlGenerator);

        $this->assertEquals($url, $this->runPrivateMethod($middleware, 'redirectTo', [$this->createRequest()]));
    }
}