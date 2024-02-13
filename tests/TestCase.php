<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Tests;

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Router;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use RossBearman\Sqids\Tests\Testbench\Models\Calamari;

class TestCase extends OrchestraTestCase
{
    protected $enablesPackageDiscoveries = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutExceptionHandling();

        $this->loadMigrationsFrom(__DIR__ . '/Testbench/database/migrations');
    }

    /**
     * Define routes setup.
     *
     * @param  Router  $router
     */
    protected function defineRoutes($router): void
    {
        $router->get('/calamari/{calamari}', function (Calamari $calamari) {
            return $calamari;
        })->middleware(SubstituteBindings::class);

        $router->get('/escargot/{calamari:slug}', function (Calamari $calamari) {
            return $calamari;
        })->middleware(SubstituteBindings::class);
    }
}
