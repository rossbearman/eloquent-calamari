<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Tests\Feature\Console\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\Concerns\WithWorkbench;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use RossBearman\Sqids\Console\Commands\AlphabetCommand;
use RossBearman\Sqids\Tests\TestCase;

final class AlphabetCommandTest extends TestCase
{
    use WithWorkbench;

    #[Test]
    public function it_can_generate_random_alphabets_for_models(): void
    {
        $status = Artisan::call(AlphabetCommand::class, [
            'models' => ['App\Models\Customer', 'App\Models\Order'],
            '--plain' => true,
        ]);
        $this->assertEquals(0, $status);

        $alphabets = collect(explode("\n", Artisan::output()))
            ->filter()
            ->mapWithKeys(function ($pair) {
                [$model, $alphabet] = explode(': ', $pair);

                return [$model => $alphabet];
            });

        $status = Artisan::call(AlphabetCommand::class, [
            'models' => ['App\Models\Customer', 'App\Models\Order'],
            '--plain' => true,
        ]);
        $this->assertEquals(0, $status);

        collect(explode("\n", Artisan::output()))
            ->filter()
            ->each(function ($pair) use ($alphabets) {
                [$model, $alphabet] = explode(': ', $pair);

                $this->assertNotEquals($alphabets[$model], $alphabet);
            });
    }

    #[Test]
    public function it_outputs_shuffled_alphabet_with_no_input(): void
    {
        $alphabet = Config::get('sqids.alphabet');

        Artisan::call(AlphabetCommand::class);

        $output = rtrim(Artisan::output());

        $this->assertNotSame($alphabet, $output);
        $this->assertSame(strlen($alphabet), strlen($output));

        foreach (str_split($alphabet) as $element) {
            $this->assertStringContainsString($element, $output);
        }
    }

    #[Test]
    #[DataProvider('modelProvider')]
    public function it_prints_config_instructions_for_models(string $model, string $envKey): void
    {
        $status = Artisan::call(AlphabetCommand::class, [
            'models' => [$model],
        ]);
        $this->assertEquals(0, $status);

        $output = Artisan::output();
        $this->assertStringContainsString("{$model}::class => env('{$envKey}')", $output);
        $this->assertStringContainsString("{$envKey}=", $output);
    }

    public static function modelProvider(): array
    {
        return [
            ['Model', 'SQIDS_ALPHABET_MODEL'],
            ['\\Model', 'SQIDS_ALPHABET_MODEL'],
            ['App\\Model', 'SQIDS_ALPHABET_MODEL'],
            ['\\App\\Model', 'SQIDS_ALPHABET_MODEL'],
            ['App\\Models\\Model', 'SQIDS_ALPHABET_MODEL'],
            ['App\\Models\\Model\\Model', 'SQIDS_ALPHABET_MODEL'],
            ['ThreeWordModel', 'SQIDS_ALPHABET_THREE_WORD_MODEL'],
            ['App\\Models\\ThreeWordModel', 'SQIDS_ALPHABET_THREE_WORD_MODEL'],
        ];
    }
}
