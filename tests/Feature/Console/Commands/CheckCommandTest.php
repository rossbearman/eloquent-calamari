<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Tests\Feature\Console\Commands;

use Illuminate\Support\Facades\Artisan;
use Orchestra\Testbench\Concerns\WithWorkbench;
use PHPUnit\Framework\Attributes\Test;
use RossBearman\Sqids\Console\Commands\CheckCommand;
use RossBearman\Sqids\Tests\TestCase;

final class CheckCommandTest extends TestCase
{
    use WithWorkbench;

    #[Test]
    public function it_can_list_the_models_that_have_fixed_alphabets(): void
    {
        config()->set('sqids.alphabets', [
            'RossBearman\Sqids\Tests\Testbench\Models\Calamari' => 'abcdefg',
            'App\Models\Order' => 'hijklmn',
        ]);

        $status = Artisan::call(CheckCommand::class);
        $this->assertEquals(0, $status);

        $output = Artisan::output();

        $this->assertStringContainsString('The following classes have alphabets defined in the config', $output);
        $this->assertStringContainsString('RossBearman\Sqids\Tests\Testbench\Models\Calamari', $output);
        $this->assertStringContainsString('abcdefg', $output);

        $this->assertStringContainsString('The following entries were found in the config, but the class could not be resolved', $output);
        $this->assertStringContainsString('App\Models\Order', $output);
        $this->assertStringContainsString('hijklmn', $output);
    }

    #[Test]
    public function it_warns_user_if_no_fixed_alphabets_are_defined(): void
    {
        $status = Artisan::call(CheckCommand::class);
        $this->assertEquals(0, $status);

        $output = Artisan::output();

        $this->assertStringContainsString('No alphabets have been defined for models', $output);
    }
}
