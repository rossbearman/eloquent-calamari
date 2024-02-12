<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Tests\Unit\Support;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RossBearman\Sqids\Exceptions\InvalidAlphabetException;
use RossBearman\Sqids\Support\Alphabet;

class AlphabetTest extends TestCase
{
    #[Test]
    #[DataProvider('alphabetProvider')]
    public function it_can_be_constructed_from_valid_alphabets(string $alphabet): void
    {
        $alphabetObject = new Alphabet($alphabet);
        $this->assertSame($alphabet, $alphabetObject->value);
    }

    #[Test]
    #[DataProvider('invalidAlphabetProvider')]
    public function it_handles_invalid_alphabet_configuration(string $alphabet, string $warning): void
    {
        try {
            $alphabetObject = new Alphabet($alphabet);
        } catch (InvalidAlphabetException $e) {
            $this->assertFalse(isset($alphabetObject));
            $this->assertSame($warning, $e->getMessage());

            return;
        }

        $this->fail('Invalid alphabet accepted.');
    }

    #[Test]
    #[DataProvider('alphabetProvider')]
    public function it_can_return_a_shuffled_copy_of_itself(string $alphabet): void
    {
        $original = new Alphabet($alphabet);
        $actual = $original->shuffle(bin2hex(random_bytes(32)));

        $this->assertNotSame($original, $actual);
        $this->assertNotEquals($original->value, $actual->value);
        $this->assertSame(strlen($alphabet), count(array_unique(str_split((string) $actual))));

        foreach (str_split($alphabet) as $element) {
            $this->assertStringContainsString($element, (string) $actual);
        }
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

    public static function invalidAlphabetProvider(): array
    {
        return [
            'single multibyte' => ['ǅ', 'alphabet must not contain multibyte characters'],
            'embedded multibyte' => ['abcҖdef', 'alphabet must not contain multibyte characters'],
            'empty string' => ['', 'alphabet must contain at least three characters'],
            'less than three characters' => ['ab', 'alphabet must contain at least three characters'],
            'non-unique characters' => ['aabcde', 'alphabet must contain unique characters'],
            'only non-unique characters' => ['aaaaa', 'alphabet must contain unique characters'],
        ];
    }
}
