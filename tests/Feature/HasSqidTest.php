<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Concerns\WithWorkbench;
use PHPUnit\Framework\Attributes\Test;
use RossBearman\Sqids\Facades\Sqids;
use RossBearman\Sqids\Tests\Testbench\Models\Calamari;
use RossBearman\Sqids\Tests\Testbench\Models\CalamariFactory;
use RossBearman\Sqids\Tests\Testbench\Models\Squad;
use RossBearman\Sqids\Tests\Testbench\Models\SquadFactory;
use RossBearman\Sqids\Tests\TestCase;

class HasSqidTest extends TestCase
{
    use RefreshDatabase;
    use WithWorkbench;

    #[Test]
    public function it_has_a_sqid_attribute_that_is_not_appended_by_default()
    {
        $modelSqid = Sqids::fromClass(Calamari::class);
        $expectedSqid = $modelSqid->encode(1);

        $entity = CalamariFactory::new()->create();

        $this->assertSame($expectedSqid, $entity->sqid);
        $this->assertArrayNotHasKey('sqid', $entity->toArray());
    }

    #[Test]
    public function it_can_append_sqid_attribute()
    {
        $expectedSqid = Sqids::fromClass(Squad::class)->encode(1);

        $entity = SquadFactory::new()->create();

        $this->assertSame($expectedSqid, $entity->toArray()['sqid']);
    }

    #[Test]
    public function it_can_be_found_by_sqid()
    {
        $entity = CalamariFactory::new()->create()->fresh();
        $sqid = $entity->sqid;

        $this->assertEquals($entity, Calamari::findBySqid($sqid));
        $this->assertEquals($entity, Calamari::findBySqidOrFail($sqid));

        $collection = Calamari::whereSqid($sqid)->get();
        $this->assertEquals(1, $collection->count());
        $this->assertEquals($entity, $collection->first());
    }

    #[Test]
    public function multiple_can_be_found_by_their_sqids()
    {
        $entities = CalamariFactory::new()->count(5)->create()->map->fresh();

        $sqids = [
            $entities[0]->sqid,
            $entities[2]->sqid,
            $entities[4]->sqid,
        ];

        $actual = Calamari::whereSqidIn($sqids)->get();

        $this->assertContainsEquals($entities[0], $actual);
        $this->assertContainsEquals($entities[2], $actual);
        $this->assertContainsEquals($entities[4], $actual);

        $this->assertNotContainsEquals($entities[1], $actual);
        $this->assertNotContainsEquals($entities[3], $actual);
    }

    #[Test]
    public function multiple_can_be_excluded_by_their_sqids()
    {
        $entities = CalamariFactory::new()->count(5)->create()->map->fresh();

        $sqids = [
            $entities[0]->sqid,
            $entities[2]->sqid,
            $entities[4]->sqid,
        ];

        $actual = Calamari::whereSqidNotIn($sqids)->get();

        $this->assertNotContainsEquals($entities[0], $actual);
        $this->assertNotContainsEquals($entities[2], $actual);
        $this->assertNotContainsEquals($entities[4], $actual);

        $this->assertContainsEquals($entities[1], $actual);
        $this->assertContainsEquals($entities[3], $actual);
    }
}
