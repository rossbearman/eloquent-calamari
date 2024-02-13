<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Tests\Testbench\Models;

use Illuminate\Database\Eloquent\Factories\Factory;

class OceanFactory extends Factory
{
    protected $model = Ocean::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Arctic',
                'Atlantic',
                'Indian',
                'Pacific',
                'Southern',
            ]),
        ];
    }
}
