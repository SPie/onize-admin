<?php

namespace App\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\ServiceProvider;

final class ApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerGuzzleClient();
    }

    private function registerGuzzleClient(): self
    {
        $this->app->singleton(ClientInterface::class, fn () => new Client([
            'base_uri' => $this->app['config']['api.baseUri']
        ]));

        return $this;
    }
}