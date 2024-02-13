<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Tests\Testbench\Models;

use Illuminate\Database\Eloquent\Model;
use RossBearman\Sqids\HasSqid;
use RossBearman\Sqids\SqidBasedRouting;

class Calamari extends Model
{
    use HasSqid, SqidBasedRouting;
}
