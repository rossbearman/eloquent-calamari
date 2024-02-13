<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Tests\Feature;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Concerns\WithWorkbench;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use RossBearman\Sqids\Tests\Testbench\Models\CalamariFactory;
use RossBearman\Sqids\Tests\TestCase;

class SqidBasedRoutingTest extends TestCase
{
    use RefreshDatabase;
    use WithWorkbench;

    #[Test]
    public function it_can_route_based_on_a_sqid()
    {
        $entities = CalamariFactory::new()->count(5)->create();

        foreach ($entities as $entity) {
            $this->get("calamari/{$entity->sqid}")
                ->assertSuccessful()
                ->assertExactJson($entity->toArray());
        }
    }

    #[Test]
    public function it_can_route_based_on_other_attributes()
    {
        $entities = CalamariFactory::new()->count(5)->create();

        foreach ($entities as $entity) {
            $this->get("escargot/{$entity->slug}")
                ->assertSuccessful()
                ->assertExactJson($entity->toArray());
        }
    }

    #[Test]
    #[DataProvider('invalidIdentifierProvider')]
    public function it_handles_an_invalid_sqid(array $identifiers)
    {
        CalamariFactory::new()->create();

        $this->expectException(ModelNotFoundException::class);

        foreach ($identifiers as $identifier) {
            $this->get("calamari/{$identifier}");
        }
    }

    public static function invalidIdentifierProvider(): array
    {
        return [
            'digits' => [[0, 1, 45234, 1234567890]],
            'symbols' => [['*+.$(!_.$-)', '*', '.']],
            'letters' => [['CkFUMZovSn', 'ivqXdxfIHP']],
            'non-existent' => [['3F7U0Lw2OH', 'RzVu62DvZp']],
            'too short' => [['3F7U0Lw2', 'RzVu62Dv']],
            'alphanumeric and symbols' => [['TBcis+9l(r', '(iTuOA)V$N']],
        ];
    }
}
