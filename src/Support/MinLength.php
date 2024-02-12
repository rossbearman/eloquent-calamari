<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Support;

use RossBearman\Sqids\Exceptions\InvalidMinLengthException;

final readonly class MinLength
{
    const LIMIT = 255;

    public function __construct(public int $value)
    {
        if ($value < 0 || $value > self::LIMIT) {
            throw new InvalidMinLengthException('min length must be between 0 and ' . self::LIMIT);
        }
    }
}
