<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Tests\Unit\Codecs;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RossBearman\Sqids\Codecs\SqidCodec;
use RossBearman\Sqids\Exceptions\InvalidSqidException;
use RossBearman\Sqids\Support\Alphabet;
use RossBearman\Sqids\Support\MinLength;

final class SqidCodecTest extends TestCase
{
    protected static string $defaultAlphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    protected static int $defaultMinLength = 10;

    #[Test]
    public function it_can_be_constructed(): void
    {
        $codec = new SqidCodec(new Alphabet(self::$defaultAlphabet), new MinLength(self::$defaultMinLength));

        $this->assertInstanceOf(SqidCodec::class, $codec);
        $this->assertSame(self::$defaultAlphabet, $codec->alphabet->value);
    }

    #[Test]
    #[DataProvider('alphabetProvider')]
    public function it_encodes_and_decodes_a_sqid(string $alphabet): void
    {
        $codec = new SqidCodec(new Alphabet($alphabet), new MinLength(self::$defaultMinLength));

        foreach (range(0, 100) as $i) {
            $originalId = fake()->numberBetween(0, PHP_INT_MAX);

            $sqid = $codec->encode($originalId);

            foreach (str_split($sqid) as $element) {
                $this->assertTrue(str_contains($alphabet, $element));
            }

            $this->assertGreaterThanOrEqual(self::$defaultMinLength, strlen($sqid));

            $id = $codec->decode($sqid);

            $this->assertSame($originalId, $id);
        }
    }

    #[Test]
    public function it_rejects_an_negative_id(): void
    {
        $codec = new SqidCodec(new Alphabet(self::$defaultAlphabet), new MinLength(self::$defaultMinLength));

        try {
            $id = -1;
            $codec->encode($id);
        } catch (\Throwable $e) {
            $this->assertSame(InvalidArgumentException::class, get_class($e));
            $this->assertSame("ID must be non-negative and less than PHP's max integer. ID: {$id}", $e->getMessage());

            return;
        }

        $this->fail('encode() accepted a negative ID');
    }

    #[Test]
    #[DataProvider('alphabetProvider')]
    public function it_rejects_an_invalid_sqid(string $alphabet): void
    {
        $fullAlphabet = self::alphabetProvider()['alphanumeric and symbols'][0];

        if ($fullAlphabet !== $alphabet) {
            $invalidAlphabet = array_diff(str_split($fullAlphabet), str_split($alphabet));
        } else {
            $invalidAlphabet = mb_str_split('۩۞ЂЖПΞͶ˦˨ʯʤɎȻǼȂɃȟ');
        }

        $codec = new SqidCodec(new Alphabet($alphabet), new MinLength(self::$defaultMinLength));

        foreach (range(0, 100) as $i) {
            $invalidSqid = implode(fake()->randomElements($invalidAlphabet, self::$defaultMinLength));

            // Default behaviour returns null
            $this->assertNull($codec->decode($invalidSqid));

            try {
                // Throw instead of returning null
                $codec->decode($invalidSqid, throwIfInvalid: true);
            } catch (\Throwable $e) {
                $this->assertSame(InvalidSqidException::class, get_class($e));
                $this->assertSame("'$invalidSqid' does not resolve to an ID", $e->getMessage());

                continue;
            }

            $this->fail('Invalid Sqid `' . $invalidSqid . '` accepted.');
        }
    }

    #[Test]
    #[DataProvider('nonCanonicalSqidProvider')]
    public function it_rejects_non_canonical_sqids(int $id, string $valid, string $invalid, string $alphabet): void
    {
        $codec = new SqidCodec(new Alphabet($alphabet), new MinLength(self::$defaultMinLength));

        $this->assertSame($valid, $codec->encode($id));
        $this->assertSame($id, $codec->decode($valid));

        // Default behaviour returns null
        $this->assertNull($codec->decode($invalid));

        try {
            // Throw instead of returning null
            $codec->decode($invalid, true);
        } catch (\Throwable $e) {
            $this->assertSame(InvalidSqidException::class, get_class($e));
            $this->assertSame("'$invalid' is not a valid representation of the returned ID", $e->getMessage());

            return;
        }

        $this->fail("accepted an invalid sqid: $invalid");
    }

    #[Test]
    #[DataProvider('nonCanonicalSqidProvider')]
    public function it_allows_non_canonical_sqids_if_check_is_disabled(int $id, string $canonicalSqid, string $nonCanonicalSqid, string $alphabet): void
    {
        $model = new SqidCodec(new Alphabet($alphabet), new MinLength(self::$defaultMinLength), isCanonical: false);

        $this->assertSame($canonicalSqid, $model->encode($id));
        $this->assertSame($id, $model->decode($canonicalSqid));
        $this->assertSame($id, $model->decode($nonCanonicalSqid));
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

    public static function nonCanonicalSqidProvider(): array
    {
        return [
            // id, valid, invalid, alphabet
            'lower' => [668338, 'jozbyswikh', 'eyjhqlracn', 'abcdefghijklmnopqrstuvwxyz'],
            'upper' => [516232852000, 'FKQBSMONCV', 'KEBRAIDGJC', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'],
            'digits' => [64, '5106798064', '2479068531', '0123456789'],
            'symbols' => [21914340, "'(!*+_.$-)", "-*\$_)'!+(.", "$-_.+!'*()"],
            'letters' => [812203665594014, 'CkFUMZovSn', 'ivqXdxfIHP', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'],
            'alphanumeric' => [6912849818214954, '3F7U0Lw2OH', 'RzVu62DvZp', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'],
            'alphanumeric and symbols' => [8225897086825830, 'TBcis+9l(r', '(iTuOA)V$N', "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789$-_.+!'*()"],
        ];
    }
}
