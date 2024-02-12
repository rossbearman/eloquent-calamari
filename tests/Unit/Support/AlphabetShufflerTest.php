<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Tests\Unit\Support;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RossBearman\Sqids\Support\AlphabetShuffler;

final class AlphabetShufflerTest extends TestCase
{
    #[Test]
    #[DataProvider('alphabetProvider')]
    public function it_shuffles_an_alphabet(string $alphabet): void
    {
        $seed = self::seedProvider()['model and APP_KEY'][0];

        $actual = (new AlphabetShuffler($seed, $alphabet))->shuffle();

        $this->assertNotSame($alphabet, $actual);
        $this->assertSame(strlen($alphabet), count(array_unique(str_split((string) $actual))));

        foreach (str_split($alphabet) as $element) {
            $this->assertStringContainsString($element, (string) $actual);
        }
    }

    #[Test]
    #[DataProvider('seedProvider')]
    public function it_produces_consistent_results_with_a_seed(string $seed): void
    {
        $alphabet = self::alphabetProvider()['alphanumeric and symbols'][0];

        $first = (new AlphabetShuffler($seed, $alphabet))->shuffle();
        $second = (new AlphabetShuffler($seed, $alphabet))->shuffle();
        $third = (new AlphabetShuffler($seed, $alphabet))->shuffle();

        $this->assertEquals($first, $second);
        $this->assertEquals($first, $third);
    }

    #[Test]
    #[DataProvider('alphabetProvider')]
    public function it_produces_different_results_with_a_different_seed(string $alphabet): void
    {
        $first = (new AlphabetShuffler('first', $alphabet))->shuffle();
        $second = (new AlphabetShuffler('second', $alphabet))->shuffle();
        $third = (new AlphabetShuffler('third', $alphabet))->shuffle();

        $this->assertNotSame($first, $second);
        $this->assertNotSame($first, $third);
        $this->assertNotSame($second, $third);
    }

    public static function alphabetProvider(): array
    {
        return [
            'lower' => ['abcdefghijklmnopqrstuvwxyz'],
            'upper' => ['ABCDEFGHIJKLMNOPQRSTUVWXYZ'],
            'digits' => ['0123456789'],
            'symbols' => ["$-_.+!'*()"],
            'letters' => ['abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'],
            'alphanumeric' => ['abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'],
            'alphanumeric and symbols' => ["abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789$-_.+!'*()"],
        ];
    }

    public static function seedProvider(): array
    {
        return [
            'model and APP_KEY' => ['User_base64:NGZocDEyZHZiYnJreXJ0MmFtaDZodTFud2pwdGs5czc='],
            'APP_KEY' => ['base64:NGZocDEyZHZiYnJreXJ0MmFtaDZodTFud2pwdGs5czc='],
            'random_bytes(10) and APP_KEY' => [bin2hex(random_bytes(10)) . '_' . 'base64:NGZocDEyZHZiYnJreXJ0MmFtaDZodTFud2pwdGs5czc='],
        ];
    }
}
