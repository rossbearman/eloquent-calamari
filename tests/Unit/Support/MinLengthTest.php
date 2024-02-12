<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Tests\Unit\Support;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RossBearman\Sqids\Exceptions\InvalidMinLengthException;
use RossBearman\Sqids\Support\MinLength;

class MinLengthTest extends TestCase
{
    #[Test]
    public function it_can_be_constructed_from_valid_input(): void
    {
        foreach (range(0, MinLength::LIMIT) as $input) {
            $this->assertSame($input, (new MinLength($input))->value);
        }
    }

    #[Test]
    #[DataProvider('invalidMinLengthProvider')]
    public function it_handles_invalid_input(int $input, string $warning): void
    {
        try {
            $minLengthObject = new MinLength($input);
        } catch (InvalidMinLengthException $e) {
            $this->assertFalse(isset($alphabetObject));
            $this->assertSame($warning, $e->getMessage());

            return;
        }

        $this->fail('Invalid min length accepted.');
    }

    public static function invalidMinLengthProvider(): array
    {
        return [
            'negative integer' => [-1, 'min length must be between 0 and ' . MinLength::LIMIT],
            'above max limit' => [MinLength::LIMIT + 1, 'min length must be between 0 and ' . MinLength::LIMIT],
        ];
    }
}
