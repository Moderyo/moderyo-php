<?php

declare(strict_types=1);

namespace Moderyo\Laravel;

use Illuminate\Support\ServiceProvider;
use Moderyo\ModeryoClient;
use Moderyo\ModeryoConfig;

/**
 * Laravel service provider for Moderyo
 */
class ModeryoServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/moderyo.php',
            'moderyo'
        );

        $this->app->singleton(ModeryoConfig::class, function ($app) {
            return new ModeryoConfig([
                'apiKey' => config('moderyo.api_key'),
                'baseUrl' => config('moderyo.base_url', 'https://api.moderyo.com'),
                'timeout' => config('moderyo.timeout', 30),
                'maxRetries' => config('moderyo.max_retries', 3),
            ]);
        });

        $this->app->singleton(ModeryoClient::class, function ($app) {
            return new ModeryoClient($app->make(ModeryoConfig::class));
        });

        $this->app->alias(ModeryoClient::class, 'moderyo');
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/moderyo.php' => config_path('moderyo.php'),
        ], 'moderyo-config');
    }
}
