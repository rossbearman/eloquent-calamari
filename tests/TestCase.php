<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Tests;

use Illuminate\Routing\Middleware\SubstituteBindings;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use RossBearman\Sqids\SqidsServiceProvider;
use RossBearman\Sqids\Tests\Testbench\Models\Calamari;
use RossBearman\Sqids\Tests\Testbench\Models\Ocean;
use RossBearman\Sqids\Tests\Testbench\Models\Squad;

class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMockingConsoleOutput();
        $this->withoutExceptionHandling();

        $this->loadMigrationsFrom(__DIR__ . '/Testbench/database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            SqidsServiceProvider::class,
        ];
    }

    protected function defineRoutes($router): void
    {
        $router->get('/calamari/{calamari}', function (Calamari $calamari) {
            return $calamari;
        })->middleware(SubstituteBindings::class);

        $router->get('/calamari/{calamari}/children/{child}', function (Calamari $calamari, Calamari $child) {
            return $child;
        })->scopeBindings()->middleware(SubstituteBindings::class);

        $router->get('/deleted/calamari/{calamari}', function (Calamari $calamari) {
            return $calamari;
        })->withTrashed()->middleware(SubstituteBindings::class);

        $router->get('/squad/{squad}', function (Squad $squad) {
            return $squad;
        })->middleware(SubstituteBindings::class);

        $router->get('/squad/{squad}/calamari/{calamari}', function (Squad $squad, Calamari $calamari) {
            return $calamari;
        })->scopeBindings()->middleware(SubstituteBindings::class);

        $router->get('/ocean/{ocean}/calamari/{calamari}', function (Ocean $ocean, Calamari $calamari) {
            return $calamari;
        })->scopeBindings()->middleware(SubstituteBindings::class);

        $router->get('/admin/calamari/{calamari:id}', function (Calamari $calamari) {
            return $calamari;
        })->middleware(SubstituteBindings::class);

        $router->get('/admin/squad/{squad}/calamari/{calamari:id}', function (Squad $squad, Calamari $calamari) {
            return $calamari;
        })->scopeBindings()->middleware(SubstituteBindings::class);

        $router->get('/escargot/{calamari:slug}', function (Calamari $calamari) {
            return $calamari;
        })->middleware(SubstituteBindings::class);
    }
}
