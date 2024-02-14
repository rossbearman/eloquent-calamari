<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Tests\Feature\Console\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\Concerns\WithWorkbench;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use RossBearman\Sqids\Console\Commands\AlphabetCommand;
use RossBearman\Sqids\Facades\Sqids;
use RossBearman\Sqids\Tests\TestCase;

final class AlphabetCommandTest extends TestCase
{
    use WithWorkbench;

    #[Test]
    public function it_can_generate_consistent_alphabets_for_models(): void
    {
        $status = Artisan::call(AlphabetCommand::class, [
            'models' => ['App\Models\Customer', 'App\Models\Order'],
            '--plain' => true,
        ]);
        $this->assertEquals(0, $status);

        $alphabets = collect(explode("\n", Artisan::output()))
            ->filter()
            ->map(function ($pair) {
                [$model, $alphabet] = explode(': ', $pair);

                return ['name' => $model, 'alphabet' => $alphabet];
            });

        $status = Artisan::call(AlphabetCommand::class, [
            'models' => ['App\Models\Customer', 'App\Models\Order'],
            '--plain' => true,
        ]);
        $this->assertEquals(0, $status);

        $output = Artisan::output();

        foreach ($alphabets as $model) {
            $this->assertStringContainsString("{$model['name']}: {$model['alphabet']}", $output);
        }
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
    public function it_outputs_consistently_shuffled_alphabet_with_key(): void
    {
        Artisan::call(AlphabetCommand::class, ['--key' => 'test-key']);
        $outputOne = Artisan::output();

        Artisan::call(AlphabetCommand::class, ['--key' => 'test-key']);
        $outputTwo = Artisan::output();

        $this->assertSame($outputOne, $outputTwo);
    }

    #[Test]
    #[DataProvider('modelProvider')]
    public function it_prints_config_instructions_for_models(string $model, string $envKey): void
    {
        $alphabet = Sqids::fromClass($model)->alphabet->value;

        $status = Artisan::call(AlphabetCommand::class, [
            'models' => [$model],
        ]);
        $this->assertEquals(0, $status);

        $output = Artisan::output();
        $this->assertStringContainsString("{$model}::class => env('{$envKey}')", $output);
        $this->assertStringContainsString("{$envKey}={$alphabet}", $output);
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
