<?php

declare(strict_types=1);

namespace RossBearman\Sqids;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use RossBearman\Sqids\Console\Commands\AlphabetCommand;
use RossBearman\Sqids\Console\Commands\CheckCommand;
use RossBearman\Sqids\Support\ConfigResolver;

final class SqidsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Sqids::class, function (Application $app) {
            return new Sqids(new ConfigResolver($app['config']['sqids']));
        });

        $this->app->when(CheckCommand::class)
            ->needs(ConfigResolver::class)
            ->give(function (Application $app) {
                return new ConfigResolver($app['config']['sqids']);
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

            $this->commands([
                AlphabetCommand::class,
                CheckCommand::class,
            ]);
        }
    }
}
