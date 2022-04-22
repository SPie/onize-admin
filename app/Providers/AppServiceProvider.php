<?php

namespace App\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this
            ->registerGuzzleClient();
    }

    private function registerGuzzleClient(): self
    {
        $this->app->bind(ClientInterface::class, fn (Container $app) => new Client());

        return $this;
    }

    public function boot(): void
    {
        //
    }
}
