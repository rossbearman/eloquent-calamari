<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Console\Commands;

use Illuminate\Console\Command;
use RossBearman\Sqids\Sqids;
use RossBearman\Sqids\Support\ConfigResolver;

final class CheckCommand extends Command
{
    protected $signature = 'sqids:check
                            { models?* : The FQDNs of the models to check }';

    protected $description = 'Check the models have a fixed alphabet registered in config';

    public function handle(ConfigResolver $config): void
    {
        /**
         * ```shell
         * php artisan sqids:check App\Models\Customer
         * ```
         *
         * If set correctly, you will see output similar to the following, without any warnings.
         *
         * ```shell
         * App\Models\Customer found in app\Models\Customer.php
         * Fixed alphabet found in config/sqids.php
         *
         * Alphabet: s7tc0TE3AfrqyMjFvbgunkhZDBp6NCIRJoQldLm8wYxHa5iWzVeP124SXUOK9G
         * ````
         */
        if (count($this->argument('models')) === 0) {
            if (empty($config->getAlphabets())) {
                $this->error('No alphabets have been defined for models in `sqids.php`');

                return;
            }

            $alphabets = [];
            foreach ($config->getAlphabets() as $model => $alphabet) {
                class_exists($model) ?
                    $alphabets['found'][] = ['model' => $model, 'alphabet' => (string) $alphabet] :
                    $alphabets['not_found'][] = ['model' => $model, 'alphabet' => (string) $alphabet];
            }

            if (!empty($alphabets['found'])) {
                $this->info('The following classes have alphabets defined in the config');
                $this->table(['Model', 'Alphabet'], $alphabets['found']);
                $this->newLine();
            }

            if (!empty($alphabets['not_found'])) {
                $this->error('The following entries were found in the config, but the class could not be resolved');
                $this->table(['Model', 'Alphabet'], $alphabets['not_found']);
                $this->newLine();
            }
        }
    }
}
