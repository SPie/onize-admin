<?php

namespace Tests\Unit\Http\Livewire\Auth;

use App\Http\Livewire\Auth\Login;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Tests\FeatureTestCase;
use Tests\Helpers\AuthHelper;
use Tests\Helpers\LaravelHelpers;
use Tests\Helpers\UsersHelper;

final class LoginTest extends FeatureTestCase
{
    use AuthHelper;
    use LaravelHelpers;
    use UsersHelper;

    private function getLogin(): Login
    {
        return new Login();
    }

    public function testProperties(): void
    {
        $login = $this->getLogin();

        $this->assertEquals('', $login->email);
        $this->assertEquals('', $login->password);
    }

    public function testRender(): void
    {
        $view = $this->createView();
        $this->mockViewLayout($view, 'layouts.signup')->once();
        $viewFactory = $this->createViewFactory();
        $this->mockViewFactoryMake($viewFactory, $view, 'livewire.auth.login');

        $this->assertEquals($view, $this->getLogin()->render($viewFactory));
    }

    private function setUpLoginTest(bool $withValidCredentials = true): array
    {
        $email = $this->getFaker()->safeEmail;
        $password = $this->getFaker()->password(12);
        $user = $this->createUser();
        $authManager = $this->createAuthManager();
        $this->mockAuthManagerLogin($authManager, $withValidCredentials ? $user : new AuthenticationException(), $email, $password);
        $login = $this->getLogin();
        $login->email = $email;
        $login->password = $password;

        return [$login, $authManager, $email, $password];
    }

    public function testLogin(): void
    {
        /** @var Login $login */
        [$login, $authManager, $email, $password] = $this->setUpLoginTest();

        $login->login($authManager);

        $this->assertAuthManagerLogin($authManager, $email, $password);
        $this->assertEquals('http://localhost', $login->redirectTo);
    }

    public function testLoginWithoutRequiredParameters(): void
    {
        /** @var Login $login */
        [$login, $authManager] = $this->setUpLoginTest();
        $login->email = '';
        $login->password = '';

        $this->expectException(ValidationException::class);

        $login->login($authManager);
    }

    public function testLoginWithoutValidEmail(): void
    {
        /** @var Login $login */
        [$login, $authManager] = $this->setUpLoginTest();
        $login->email = $this->getFaker()->word;

        $this->expectException(ValidationException::class);

        $login->login($authManager);
    }

    public function testLoginWithoutValidCredentials(): void
    {
        /** @var Login $login */
        [$login, $authManager] = $this->setUpLoginTest(withValidCredentials: false);

        $login->login($authManager);

        $this->assertEquals(['validation.invalid-credentials'], $login->getErrorBag()->get('email'));
    }
}