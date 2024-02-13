<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Tests\Testbench\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CalamariFactory extends Factory
{
    protected $model = Calamari::class;

    public function definition(): array
    {
        $name = fake()->unique()->firstName();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
