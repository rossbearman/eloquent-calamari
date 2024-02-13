<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Builder;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use RossBearman\Sqids\Facades\Sqids;

/**
 * @template TModelClass of Model
 */
class SqidScope implements Scope
{
    /**
     * @param  Builder<TModelClass>  $builder
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('whereSqid', function (Builder $builder, string $sqid) {
            $model = $builder->getModel();

            return $builder->where(
                $model->qualifyColumn($model->getKeyName()),
                Sqids::fromModel($model)->decode($sqid)
            );
        });

        $builder->macro('whereSqidIn', function (Builder $builder, array $sqids, $boolean = 'and', $not = false) {
            $model = $builder->getModel();

            return $builder->whereIn(
                $model->qualifyColumn($model->getKeyName()),
                array_map(fn (string $sqid) => Sqids::fromModel($model)->decode($sqid), $sqids),
                $boolean,
                $not,
            );
        });

        $builder->macro('whereSqidNotIn', function (Builder $builder, array $sqids, $boolean = 'and') {
            return $builder->whereSqidIn($sqids, $boolean, not: true);
        });

        $builder->macro('findBySqid', function (Builder $builder, string $sqid) {
            return $builder->whereSqid($sqid)->first();
        });

        $builder->macro('findBySqidOrFail', function (Builder $builder, string $sqid) {
            return $builder->whereSqid($sqid)->firstOrFail();
        });
    }

    /**
     * @param  Builder<TModelClass>  $builder
     */
    public function apply(Builder $builder, Model $model)
    {
    }
}
