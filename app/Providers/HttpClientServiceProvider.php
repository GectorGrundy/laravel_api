<?php
declare(strict_types=1);

namespace App\Providers;

use GuzzleHttp\Client as Guzzle;
use Illuminate\Support\ServiceProvider;
use GuzzleHttp\ClientInterface as GuzzleInterface;

class HttpClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Guzzle::class, function () {
            return new Guzzle([
                'http_errors' => false
            ]);
        });

        $this->app->alias(Guzzle::class, GuzzleInterface::class);
    }
}