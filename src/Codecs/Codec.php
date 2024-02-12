<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Codecs;

interface Codec
{
    public function encode(int $id): string;

    public function decode(string $sqid): ?int;
}
