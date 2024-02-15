<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RossBearman\Sqids\Sqids;

final class AlphabetCommand extends Command
{
    protected $signature = 'sqids:alphabet
                            { models?* : The FQDNs of the models to generate an alphabet for }
                            { --P|plain : Get key pairs without any instructions }';

    protected $description = 'Generate a new alphabet for a specific model';

    public function handle(Sqids $sqids): void
    {
        if (count($this->argument('models')) === 0) {
            $this->info((string) $sqids->shuffleDefaultAlphabet(bin2hex(random_bytes(32))));

            return;
        }

        $alphabets = collect($this->argument('models'))->mapWithKeys(
            fn (string $model) => [$model => (string) $sqids->shuffleDefaultAlphabet(bin2hex(random_bytes(32)))]
        );

        $this->option('plain') ? $this->printPlain($alphabets) : $this->printInstructions($alphabets);
    }

    /** @param Collection<string, string> $alphabets */
    protected function printPlain(Collection $alphabets): void
    {
        foreach ($alphabets as $model => $alphabet) {
            $this->info("{$model}: {$alphabet}");
        }
    }

    /** @param Collection<string, string> $alphabets */
    protected function printInstructions(Collection $alphabets): void
    {
        if (!file_exists(config_path('sqids.php'))) {
            $this->warn('Publish the Eloquent Calamari config file:');
            $this->newLine();
            $this->info('php artisan vendor:publish --provider="RossBearman\Sqids\SqidsServiceProvider"');
            $this->newLine();
        }

        $this->warn('Update your `config/sqids.php` file to include the following items:');
        $this->newLine();
        $this->info("'alphabets' => [");

        foreach ($alphabets as $model => $alphabet) {
            $this->info('    ' . $model . "::class => env('{$this->getEnvKey($model)}'),");
        }

        $this->info('];');
        $this->newLine();
        $this->warn('And add these keys to your .env:');
        $this->newLine();

        foreach ($alphabets as $model => $alphabet) {
            $this->info("{$this->getEnvKey($model)}={$alphabet}");
        }
    }

    protected function getModelName(string $model): string
    {
        return Str::afterLast($model, '\\');
    }

    protected function getEnvKey(string $model): string
    {
        return 'SQIDS_ALPHABET_' . Str::of($this->getModelName($model))->snake()->upper();
    }
}
