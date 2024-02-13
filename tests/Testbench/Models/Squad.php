<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Tests\Testbench\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RossBearman\Sqids\HasSqid;
use RossBearman\Sqids\SqidBasedRouting;

class Squad extends Model
{
    use HasSqid, SqidBasedRouting;

    protected $appends = [
        'sqid',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function calamaris(): HasMany
    {
        return $this->hasMany(Calamari::class);
    }
}
