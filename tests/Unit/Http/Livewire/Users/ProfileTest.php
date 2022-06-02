<?php

namespace Tests\Unit\Http\Livewire\Users;

use App\Http\Livewire\Users\Profile;
use Tests\FeatureTestCase;
use Tests\Helpers\AuthHelper;
use Tests\Helpers\LaravelHelpers;
use Tests\Helpers\ReflectionHelper;
use Tests\Helpers\UsersHelper;

final class ProfileTest extends FeatureTestCase
{
    use AuthHelper;
    use LaravelHelpers;
    use ReflectionHelper;
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
        $this->assertEquals($user, $this->getPrivateProperty($profile, 'user'));
    }

    public function testRender(): void
    {
        $view = $this->createView();
        $viewFactory = $this->createViewFactory();
        $this->mockViewFactoryMake($viewFactory, $view, 'livewire.users.profile');

        $this->assertEquals($view, $this->getProfile()->render($viewFactory));
    }
}