<?php

declare(strict_types=1);

namespace RossBearman\Sqids;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use RossBearman\Sqids\Support\ConfigResolver;

final class SqidsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Sqids::class, function (Application $app) {
            return new Sqids(new ConfigResolver($app['config']['sqids']));
        });
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/sqids.php', 'sqids'
        );

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/sqids.php' => config_path('sqids.php'),
            ]);
        }
    }
}
