<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use RossBearman\Sqids\Codecs\SqidCodec;
use RossBearman\Sqids\Exceptions\InvalidAlphabetException;
use RossBearman\Sqids\Exceptions\InvalidConfigException;
use RossBearman\Sqids\Exceptions\InvalidMinLengthException;
use RossBearman\Sqids\Sqids;
use RossBearman\Sqids\Support\MinLength;
use RossBearman\Sqids\Tests\Testbench\Models\Calamari;
use RossBearman\Sqids\Tests\TestCase;
use TypeError;

final class SqidsTest extends TestCase
{
    #[Test]
    public function it_can_be_constructed_with_default_config(): void
    {
        $sqids = app()->make(Sqids::class);

        $this->assertInstanceOf(Sqids::class, $sqids);
        $this->assertSame(config('sqids.alphabet'), $sqids->getDefaultAlphabet()->value);
        $this->assertSame(config('sqids.min_length'), $sqids->getDefaultMinLength()->value);
        $this->assertSame(str_replace('base64:', '', config('sqids.key')), $sqids->getKey());
    }

    #[Test]
    public function it_can_shuffle_the_alphabet(): void
    {
        $alphabet = config('sqids.alphabet');

        $sqids = app()->make(Sqids::class);
        $actual = $sqids->shuffleDefaultAlphabet(bin2hex(random_bytes(32)))->value;

        $this->assertNotSame($alphabet, $actual);
        $this->assertSame(strlen($alphabet), strlen($actual));

        foreach (str_split($alphabet) as $element) {
            $this->assertStringContainsString($element, $actual);
        }
    }

    #[Test]
    #[DataProvider('modelProvider')]
    public function it_can_create_and_retrieve_a_sqid_codec_for_a_model(string $model)
    {
        $sqids = app()->make(Sqids::class);

        $sqidCodec = $sqids->fromClass($model);
        $this->assertInstanceOf(SqidCodec::class, $sqidCodec);

        $this->assertSame($sqidCodec, $sqids->fromClass($model));
    }

    #[Test]
    #[DataProvider('modelProvider')]
    public function it_consistently_shuffles_the_alphabet_for_a_model(string $model): void
    {
        $sqids = app()->make(Sqids::class);

        $alphabet = $sqids->getDefaultAlphabet()->value;
        $actual = $sqids->shuffleAlphabetFor($model)->value;
        $actualRepeat = $sqids->shuffleAlphabetFor($model)->value;

        $this->assertSame($actual, $actualRepeat);

        $this->assertNotSame($alphabet, $actual);
        $this->assertSame(strlen($alphabet), strlen($actual));

        foreach (str_split($alphabet) as $element) {
            $this->assertStringContainsString($element, $actual);
        }
    }

    #[Test]
    public function it_uses_model_specific_alphabet(): void
    {
        $alphabets = [
            'App\\Models\\Model' => 'ABCDEFG',
            'App\\Models\\ModelTwo' => 'HIJKLMN',
            'App\\Models\\ModelThree' => 'OPQRSTU',
        ];

        config()->set('sqids.alphabets', $alphabets);

        $sqids = app()->make(Sqids::class);

        foreach ($alphabets as $model => $alphabet) {
            $actual = $sqids->fromClass($model)->alphabet->value;

            $this->assertSame($alphabet, $actual);
        }
    }

    #[Test]
    #[DataProvider('invalidAlphabetProvider')]
    public function it_throws_with_invalid_alphabet_configuration(mixed $alphabet, string $warning): void
    {
        $defaultAlphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        config()->set('sqids.alphabet', $alphabet);

        try {
            $sqids = app()->make(Sqids::class);
        } catch (InvalidAlphabetException|InvalidConfigException $e) {
            $this->assertFalse(isset($sqids));
            $this->assertStringContainsString($warning, $e->getMessage());

            return;
        }

        $this->fail('Invalid alphabet accepted.');
    }

    #[Test]
    #[DataProvider('invalidMinLengthProvider')]
    public function it_throws_with_invalid_min_length_configuration(mixed $minLength, string $warning): void
    {
        $defaultMinLength = 10;
        config()->set('sqids.min_length', $minLength);

        try {
            $sqids = app()->make(Sqids::class);
        } catch (InvalidMinLengthException|InvalidConfigException $e) {
            $this->assertFalse(isset($sqids));
            $this->assertStringContainsString($warning, $e->getMessage());

            return;
        }

        $this->fail('Invalid min length accepted.');
    }

    #[Test]
    #[DataProvider('invalidKeyProvider')]
    public function it_throws_with_invalid_key_configuration(mixed $key, string $message): void
    {
        config()->set('sqids.key', $key);

        try {
            $sqids = app()->make(Sqids::class);
        } catch (InvalidConfigException $e) {
            $this->assertSame($message, $e->getMessage());

            return;
        }

        $this->fail('Invalid key accepted.');
    }

    #[Test]
    public function it_strips_base64_from_the_default_key(): void
    {
        config()->set('app.key', 'base64:d2QzM2lxaGVyenhtNGFycWFyeXhwcXhiZ3ZjeGRnN3U=');

        $sqids = app()->make(Sqids::class);

        $this->assertSame('d2QzM2lxaGVyenhtNGFycWFyeXhwcXhiZ3ZjeGRnN3U=', $sqids->getKey());
    }

    #[Test]
    public function key_can_be_set_after_creation(): void
    {
        $sqids = app()->make(Sqids::class);

        $sqids->setKey('new-key');
        $this->assertSame('new-key', $sqids->getKey());

        $sqids->setKey('base64:key');
        $this->assertSame('key', $sqids->getKey());
    }

    #[Test]
    public function alphabet_can_be_set_after_creation(): void
    {
        $sqids = app()->make(Sqids::class);

        $sqids->setDefaultAlphabet('abcdef');
        $this->assertSame('abcdef', $sqids->getDefaultAlphabet()->value);
    }

    #[Test]
    #[DataProvider('invalidAlphabetProvider')]
    public function invalid_alphabet_cannot_be_set_after_creation(mixed $invalidAlphabet, string $warning): void
    {
        $sqids = app()->make(Sqids::class);

        $original = $sqids->getDefaultAlphabet();

        try {
            $sqids->setDefaultAlphabet($invalidAlphabet);
        } catch (InvalidAlphabetException|TypeError $e) {
            $this->assertStringContainsString($warning, $e->getMessage());
            $this->assertSame($original, $sqids->getDefaultAlphabet());

            return;
        }

        $this->fail('Invalid ' . gettype($invalidAlphabet) . ' accepted.');
    }

    #[Test]
    public function min_length_can_be_set_after_creation(): void
    {
        $sqids = app()->make(Sqids::class);

        $sqids->setDefaultMinLength(1);
        $this->assertSame(1, $sqids->getDefaultMinLength()->value);
    }

    #[Test]
    #[DataProvider('invalidMinLengthProvider')]
    public function invalid_min_length_cannot_be_set_after_creation(mixed $invalidMinLength, string $warning): void
    {
        $sqids = app()->make(Sqids::class);

        $original = $sqids->getDefaultMinLength();

        try {
            $sqids->setDefaultMinLength($invalidMinLength);
        } catch (InvalidMinLengthException|TypeError $e) {
            $this->assertStringContainsString($warning, $e->getMessage());
            $this->assertSame($original, $sqids->getDefaultMinLength());

            return;
        }

        $this->fail('Invalid ' . gettype($invalidMinLength) . ' accepted.');
    }

    public static function modelProvider(): array
    {
        return [
            [Calamari::class],
            ['Model', 'Model'],
            ['\\Model', 'Model'],
            ['App\\Model', 'Model'],
            ['\\App\\Model', 'Model'],
            ['App\\Models\\Model', 'Model'],
            ['App\\Models\\Model\\Model', 'Model'],
        ];
    }

    public static function invalidAlphabetProvider(): array
    {
        return [
            'integer' => [0, 'string, int given'],
            'single multibyte' => ['ǅ', 'alphabet must not contain multibyte characters'],
            'embedded multibyte' => ['abcҖdef', 'alphabet must not contain multibyte characters'],
            'empty string' => ['', 'alphabet must contain at least three characters'],
            'less than three characters' => ['ab', 'alphabet must contain at least three characters'],
            'non-unique characters' => ['aabcde', 'alphabet must contain unique characters'],
            'only non-unique characters' => ['aaaaa', 'alphabet must contain unique characters'],
        ];
    }

    public static function invalidMinLengthProvider(): array
    {
        return [
            'string' => ['1', 'must be of type int, string given'],
            'negative integer' => [-1, 'min length must be between 0 and ' . MinLength::LIMIT],
            'above max limit' => [MinLength::LIMIT + 1, 'min length must be between 0 and ' . MinLength::LIMIT],
        ];
    }

    public static function invalidKeyProvider(): array
    {
        return [
            'null' => [null, 'a key must be set in the configuration'],
            'integer' => [123, 'the key set in `sqids.php` must be a string'],
        ];
    }
}
