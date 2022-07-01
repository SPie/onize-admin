<?php

namespace Tests\Unit\Http\Livewire\Users;

use App\Http\Livewire\Users\Profile;
use Illuminate\Validation\ValidationException;
use Tests\FeatureTestCase;
use Tests\Helpers\AuthHelper;
use Tests\Helpers\LaravelHelpers;
use Tests\Helpers\UsersHelper;

final class ProfileTest extends FeatureTestCase
{
    use AuthHelper;
    use LaravelHelpers;
    use UsersHelper;

    private function getProfile(): Profile
    {
        return new Profile();
    }

    private function setUpMountTest(): array
    {
        $user = $this->createUser();
        $authManager = $this->createAuthManager();
        $this->mockAuthManagerAuthenticatedUser($authManager, $user);
        $profile = $this->getProfile();

        return [$profile, $authManager, $user];
    }

    public function testMount(): void
    {
        /** @var Profile $profile */
        [$profile, $authManager, $user] = $this->setUpMountTest();

        $profile->mount($authManager);

        $this->assertEquals($user->getEmail(), $profile->email);
    }

    public function testRender(): void
    {
        $view = $this->createView();
        $viewFactory = $this->createViewFactory();
        $this->mockViewFactoryMake($viewFactory, $view, 'livewire.users.profile');

        $this->assertEquals($view, $this->getProfile()->render($viewFactory));
    }

    private function setUpEditEmailTest(): array
    {
        $email = $this->getFaker()->safeEmail;
        $user = $this->createUser(null, $email);
        $userManager = $this->createUserManager();
        $this->mockUserMangerEditProfile($userManager, $user, $email);
        $profile = $this->getProfile();
        $profile->editMode = true;
        $profile->email = $email;

        return [$profile, $userManager, $user, $email];
    }

    public function testEditEmail(): void
    {
        /** @var Profile $profile */
        [$profile, $userManager, $user, $email] = $this->setUpEditEmailTest();

        $profile->editEmail($userManager);

        $this->assertUserMangerEditProfile($userManager, $email);
        $this->assertEquals($email, $profile->email);
        $this->assertFalse($profile->editMode);
    }

    public function testEditEmailWithoutEmail(): void
    {
        /** @var Profile $profile */
        [$profile, $userManager] = $this->setUpEditEmailTest();
        $profile->email = '';

        $this->expectException(ValidationException::class);

        $profile->editEmail($userManager);
    }

    public function testEditEmailWithInvalidEmail(): void
    {
        /** @var Profile $profile */
        [$profile, $userManager] = $this->setUpEditEmailTest();
        $profile->email = $this->getFaker()->word;

        $this->expectException(ValidationException::class);

        $profile->editEmail($userManager);
    }
}