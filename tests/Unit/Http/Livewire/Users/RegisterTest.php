<?php

namespace Tests\Unit\Http\Livewire\Users;

use App\Http\Livewire\Users\Register;
use Illuminate\Validation\ValidationException;
use Tests\FeatureTestCase;
use Tests\Helpers\AuthHelper;
use Tests\Helpers\LaravelHelpers;
use Tests\Helpers\UsersHelper;

final class RegisterTest extends FeatureTestCase
{
    use AuthHelper;
    use LaravelHelpers;
    use UsersHelper;

    private function getRegister(): Register
    {
        return new Register();
    }

    public function testRender(): void
    {
        $view = $this->createView();
        $this->mockViewLayout($view, 'layouts.signup')->once();
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
        $register = $this->getRegister();
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
