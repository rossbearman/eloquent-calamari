<?php

declare(strict_types=1);

namespace RossBearman\Sqids;

use Illuminate\Support\Str;

trait SqidBasedRouting
{
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        $field ??= $this->getRouteKeyName();

        if ($field && Str::afterLast($field, '.') !== 'sqid') {
            return parent::resolveRouteBindingQuery($query, $value, $field);
        }

        return $this->whereSqid(sqid: $value);
    }

    public function getRouteKeyName(): string
    {
        return 'sqid';
    }
}
