<?php

namespace App\Http\Middleware;

use App\Http\Livewire\Auth\Login;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Contracts\Routing\UrlGenerator;

class Authenticate extends Middleware
{
    private UrlGenerator $urlGenerator;

    public function __construct(Auth $auth, UrlGenerator $urlGenerator)
    {
        parent::__construct($auth);

        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        return $this->urlGenerator->route(Login::NAME_LOGIN);
    }
}
