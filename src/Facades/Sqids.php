<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Facades;

use Illuminate\Support\Facades\Facade;

/** @mixin \RossBearman\Sqids\Sqids */
class Sqids extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \RossBearman\Sqids\Sqids::class;
    }
}
