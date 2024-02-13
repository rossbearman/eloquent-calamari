<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Tests\Feature;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Concerns\WithWorkbench;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use RossBearman\Sqids\Tests\Testbench\Models\CalamariFactory;
use RossBearman\Sqids\Tests\Testbench\Models\OceanFactory;
use RossBearman\Sqids\Tests\Testbench\Models\SquadFactory;
use RossBearman\Sqids\Tests\TestCase;

class SqidBasedRoutingTest extends TestCase
{
    use RefreshDatabase;
    use WithWorkbench;

    #[Test]
    public function it_can_route_based_on_a_sqid()
    {
        $calamaris = CalamariFactory::new()->count(5)->create();

        foreach ($calamaris as $calamari) {
            $this->assertSame($calamari->sqid, $calamari->getRouteKey());

            $this->get("calamari/{$calamari->sqid}")
                ->assertSuccessful()
                ->assertJson(['id' => $calamari->id]);
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

    #[Test]
    public function it_can_route_based_on_other_attributes()
    {
        $calamaris = CalamariFactory::new()->count(5)->create();

        foreach ($calamaris as $calamari) {
            $this->get("admin/calamari/{$calamari->id}")
                ->assertSuccessful()
                ->assertJson(['id' => $calamari->id]);

            $this->get("escargot/{$calamari->slug}")
                ->assertSuccessful()
                ->assertJson(['id' => $calamari->id]);
        }
    }

    #[Test]
    public function it_can_route_through_children()
    {
        $calamari = CalamariFactory::new()->has(
            CalamariFactory::new()->count(5), 'children'
        )->create();

        foreach ($calamari->children as $child) {
            $this->get("calamari/{$child->parent->sqid}/children/{$child->sqid}")
                ->assertSuccessful()
                ->assertJson(['id' => $child->id]);
        }
    }

    #[Test]
    public function it_can_route_through_children_using_has_many_through()
    {
        $ocean =
            OceanFactory::new()->has(
                SquadFactory::new()->has(
                    CalamariFactory::new()->count(5)
                )
            )->create();

        foreach ($ocean->calamaris as $calamari) {
            $this->get("ocean/{$ocean->sqid}/calamari/{$calamari->sqid}")
                ->assertSuccessful()
                ->assertJson(['id' => $calamari->id]);
        }
    }

    #[Test]
    public function it_can_route_through_children_with_other_attributes()
    {
        $squad = SquadFactory::new()->has(
            CalamariFactory::new()->count(5)
        )->create();

        foreach ($squad->calamaris as $calamari) {
            $this->get("admin/squad/{$calamari->squad->slug}/calamari/{$calamari->id}")
                ->assertSuccessful()
                ->assertJson(['id' => $calamari->id]);
        }
    }

    #[Test]
    public function it_handles_route_key_being_overridden()
    {
        $squad = SquadFactory::new()->create();

        $this->get("squad/{$squad->slug}")
            ->assertSuccessful()
            ->assertJson(['id' => $squad->id]);
    }

    #[Test]
    public function it_handles_soft_deletes()
    {
        $calamaris = CalamariFactory::new()->deleted()->count(5)->create();

        foreach ($calamaris as $calamari) {
            $this->get("deleted/calamari/{$calamari->sqid}")
                ->assertSuccessful()
                ->assertJson(['id' => $calamari->id]);

            $this->expectException(ModelNotFoundException::class);
            $this->get("calamari/{$calamari->sqid}");
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
