<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Tests\Testbench\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use RossBearman\Sqids\HasSqid;
use RossBearman\Sqids\SqidBasedRouting;

class Ocean extends Model
{
    use HasSqid, SqidBasedRouting;

    /** @return HasMany<Squad, $this> */
    public function squads(): HasMany
    {
        return $this->hasMany(Squad::class);
    }

    /** @return HasManyThrough<Calamari, Squad, $this> */
    public function calamaris(): HasManyThrough
    {
        return $this->hasManyThrough(Calamari::class, Squad::class);
    }
}
