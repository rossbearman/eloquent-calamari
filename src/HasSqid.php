<?php

declare(strict_types=1);

namespace RossBearman\Sqids;

use Illuminate\Database\Eloquent\Casts\Attribute;
use RossBearman\Sqids\Builder\SqidScope;
use RossBearman\Sqids\Facades\Sqids;

trait HasSqid
{
    public static function bootHasSqid(): void
    {
        static::addGlobalScope(new SqidScope);
    }

    protected function sqid(): Attribute
    {
        return Attribute::make(
            get: fn () => Sqids::sqidForModel($this),
        );
    }
}
