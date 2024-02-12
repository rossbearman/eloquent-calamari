<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Support;

use RossBearman\Sqids\Exceptions\InvalidAlphabetException;
use Stringable;

final readonly class Alphabet implements Stringable
{
    public function __construct(public string $value)
    {
        if (mb_strlen($value) !== strlen($value)) {
            throw new InvalidAlphabetException('alphabet must not contain multibyte characters');
        }

        if (strlen($value) < 3) {
            throw new InvalidAlphabetException('alphabet must contain at least three characters');
        }

        if (count(array_unique(str_split($value))) !== strlen($value)) {
            throw new InvalidAlphabetException('alphabet must contain unique characters');
        }
    }

    public function shuffle(string $seed): Alphabet
    {
        return (new AlphabetShuffler($seed, $this))->shuffle();
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
