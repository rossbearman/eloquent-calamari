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
        $router->get('/calamari/{calamari}', fn (Calamari $calamari) => $calamari)
            ->middleware(SubstituteBindings::class);

        $router->get('/calamari/{calamari}/children/{child}', fn (Calamari $calamari, Calamari $child) => $child)
            ->middleware(SubstituteBindings::class)
            ->scopeBindings();

        $router->get('/deleted/calamari/{calamari}', fn (Calamari $calamari) => $calamari)
            ->middleware(SubstituteBindings::class)
            ->withTrashed();

        $router->get('/squad/{squad}', fn (Squad $squad) => $squad)
            ->middleware(SubstituteBindings::class);

        $router->get('/squad/{squad}/calamari/{calamari}', fn (Squad $squad, Calamari $calamari) => $calamari)
            ->middleware(SubstituteBindings::class)
            ->scopeBindings();

        $router->get('/ocean/{ocean}/calamari/{calamari}', fn (Ocean $ocean, Calamari $calamari) => $calamari)
            ->middleware(SubstituteBindings::class)
            ->scopeBindings();

        $router->get('/admin/calamari/{calamari:id}', fn (Calamari $calamari) => $calamari)
            ->middleware(SubstituteBindings::class);

        $router->get('/admin/squad/{squad}/calamari/{calamari:id}', fn (Squad $squad, Calamari $calamari) => $calamari)
            ->middleware(SubstituteBindings::class)
            ->scopeBindings();

        $router->get('/escargot/{calamari:slug}', fn (Calamari $calamari) => $calamari)
            ->middleware(SubstituteBindings::class);
    }
}
