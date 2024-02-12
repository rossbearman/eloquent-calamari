<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Support;

use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;
use RossBearman\Sqids\Exceptions\InvalidAlphabetException;

final readonly class AlphabetShuffler
{
    /** @var array<int, string> */
    protected array $alphabet;

    protected Randomizer $randomizer;

    public function __construct(string $seed, string|Alphabet $alphabet)
    {
        $this->alphabet = str_split((string) $alphabet);

        $this->randomizer = new Randomizer(new Xoshiro256StarStar($this->normaliseSeed($seed)));
    }

    /**
     * @throws InvalidAlphabetException
     */
    public function shuffle(): Alphabet
    {
        return new Alphabet(implode($this->randomizer->shuffleArray($this->alphabet)));
    }

    /**
     * Ensure the seed is exactly 32 bytes. Either by cutting it to length, or padding it by repeating from the start.
     */
    protected function normaliseSeed(string $seed): string
    {
        // Stripping 'base64:' helps to increase entropy when using Laravel's APP_KEY in the seed
        $seed = str_replace('base64:', '', $seed);

        while (mb_strlen($seed) < 32) {
            $seed = $seed . mb_strcut($seed, 0, 32 - mb_strlen($seed));
        }

        return mb_strcut($seed, 0, 32);
    }
}
