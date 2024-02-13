<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Tests\Testbench\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SquadFactory extends Factory
{
    protected $model = Squad::class;

    public function definition(): array
    {
        $country = fake()->unique()->country();

        return [
            'location' => $country,
            'slug' => Str::slug($country),
        ];
    }
}
