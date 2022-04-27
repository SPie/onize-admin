<?php

namespace Tests\Unit\Http\Livewire\Users;

use App\Auth\AuthManager;
use App\Http\Livewire\Users\Register;
use App\Users\UserManager;
use Illuminate\Validation\ValidationException;
use Livewire\Redirector;
use Mockery as m;
use Mockery\MockInterface;
use Tests\FeatureTestCase;
use Tests\Helpers\AuthHelper;
use Tests\Helpers\LaravelHelpers;
use Tests\Helpers\UsersHelper;

final class RegisterTest extends FeatureTestCase
{
    use AuthHelper;
    use LaravelHelpers;
    use UsersHelper;

    /**
     * @return Register|MockInterface
     */
    private function getRegister(
        UserManager $userManager = null,
        AuthManager $authManager = null,
        Redirector $redirector = null
    ): Register {
        $register = m::mock(Register::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $register->mount(
            $userManager ?: $this->createUserManager(),
            $authManager ?: $this->createAuthManager(),
            $redirector ?: $this->createLivewireRedirector()
        );

        return $register;
    }

    public function testRender(): void
    {
        $view = $this->createView();
        $viewFactory = $this->createViewFactory();
        $this->mockViewFactoryMake($viewFactory, $view, 'livewire.users.register');

        $this->assertEquals($view, $this->getRegister()->render($viewFactory));
    }

    public function testProperties(): void
    {
        $register = $this->getRegister();

        $this->assertEquals('', $register->email);
        $this->assertEquals('', $register->password);
        $this->assertEquals('', $register->passwordConfirm);
    }

    private function setUpRegisterTest(
        bool $withRequiredFields = true,
        bool $withValidEmail = true,
        bool $withValidPassword = true,
        bool $withMatchingPasswords = true
    ): array {
        $email = $withValidEmail ? $this->getFaker()->safeEmail : $this->getFaker()->word;
        $password = $withValidPassword ? $this->getFaker()->password(12) : $this->getFaker()->password(1, 11);
        $passwordConfirm = $password . ($withMatchingPasswords ? '' : $this->getFaker()->word);
        $user = $this->createUser();
        $userManager = $this->createUserManager();
        $this->mockUserManagerRegister($userManager, $user, $email, $password);
        $authManager = $this->createAuthManager();
        $response = $this->createRedirectResponse();
        $redirector = $this->createLivewireRedirector();
        $this->mockLivewireRedirectorRoute($redirector, $response, 'home');
        $register = $this->getRegister($userManager, $authManager, $redirector);
        if ($withRequiredFields) {
            $register->email = $email;
            $register->password = $password;
            $register->passwordConfirm = $passwordConfirm;
        }

        return [$register, $userManager, $authManager, $authManager, $user];
    }

    public function testRegister(): void
    {
        /** @var Register $register */
        [$register, $userManager, $authManager, $authManager, $user] = $this->setUpRegisterTest();

        $register->register($userManager, $authManager);

        $this->assertAuthManagerLoginUser($authManager, $user);
    }

    public function testRegisterWithoutRequiredFields(): void
    {
        /** @var Register $register */
        [$register, $userManager, $authManager] = $this->setUpRegisterTest(withRequiredFields: false);

        $this->expectException(ValidationException::class);

        $register->register($userManager, $authManager);
    }

    public function testRegisterWithoutValidEmail(): void
    {
        /** @var Register $register */
        [$register, $userManager, $authManager] = $this->setUpRegisterTest(withValidEmail: false);

        $this->expectException(ValidationException::class);

        $register->register($userManager, $authManager);
    }

    public function testRegisterWithoutInvalidPassword(): void
    {
        /** @var Register $register */
        [$register, $userManager, $authManager] = $this->setUpRegisterTest(withValidPassword: false);

        $this->expectException(ValidationException::class);

        $register->register($userManager, $authManager);
    }

    public function testRegisterWithoutMatchingPasswords(): void
    {
        /** @var Register $register */
        [$register, $userManager, $authManager] = $this->setUpRegisterTest(withMatchingPasswords: false);

        $this->expectException(ValidationException::class);

        $register->register($userManager, $authManager);
    }
}
