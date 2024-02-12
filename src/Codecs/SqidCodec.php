<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Codecs;

use InvalidArgumentException;
use RossBearman\Sqids\Exceptions\InvalidSqidException;
use RossBearman\Sqids\Support\Alphabet;
use RossBearman\Sqids\Support\MinLength;
use Sqids\Sqids;

final readonly class SqidCodec implements Codec
{
    public Alphabet $alphabet;

    protected bool $isCanonical;

    protected Sqids $sqids;

    /** @param array<int, string> $blocklist */
    public function __construct(
        Alphabet $alphabet, MinLength $minLength, bool $isCanonical = true, array $blocklist = []
    ) {
        $this->alphabet = $alphabet;

        $this->isCanonical = $isCanonical;

        $this->sqids = new Sqids(
            $alphabet->value, $minLength->value, array_merge(Sqids::DEFAULT_BLOCKLIST, $blocklist)
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function encode(int $id): string
    {
        if ($id < 0 || $id > PHP_INT_MAX) {
            throw new InvalidArgumentException("ID must be non-negative and less than PHP's max integer. ID: {$id}");
        }

        return $this->sqids->encode([$id]);
    }

    /**
     * @throws InvalidSqidException
     */
    public function decode(string $sqid, bool $throwIfInvalid = false): ?int
    {
        $id = $this->sqids->decode($sqid)[0] ?? null;

        if ($id === null) {
            !$throwIfInvalid ?: throw new InvalidSqidException("'$sqid' does not resolve to an ID");

            return null;
        }

        if ($this->isCanonical && $this->encode($id) !== $sqid) {
            !$throwIfInvalid ?: throw new InvalidSqidException("'$sqid' is not a valid representation of the returned ID");

            return null;
        }

        return $id;
    }
}
