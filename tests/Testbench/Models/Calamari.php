<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Tests\Testbench\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use RossBearman\Sqids\HasSqid;
use RossBearman\Sqids\SqidBasedRouting;

class Calamari extends Model
{
    use HasSqid, SqidBasedRouting;
    use SoftDeletes;

    public function squad(): BelongsTo
    {
        return $this->belongsTo(Squad::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Calamari::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Calamari::class, 'parent_id');
    }
}
