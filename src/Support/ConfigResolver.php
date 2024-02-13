<?php

declare(strict_types=1);

namespace RossBearman\Sqids\Support;

use Illuminate\Database\Eloquent\Model;
use RossBearman\Sqids\Exceptions\InvalidAlphabetException;
use RossBearman\Sqids\Exceptions\InvalidConfigException;
use RossBearman\Sqids\Exceptions\InvalidMinLengthException;

class ConfigResolver
{
    const DEFAULT_ALPHABET = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    const DEFAULT_MIN_LENGTH = 10;

    protected Alphabet $alphabet;

    protected MinLength $minLength;

    protected string $key;

    /** @var array<string, bool> */
    protected array $canonicalChecks = [];

    /** @var array<string, Alphabet> */
    protected array $modelAlphabets = [];

    /** @var array<string, MinLength> */
    protected array $modelMinLengths = [];

    /**
     * @param array{
     *     'alphabet': string,
     *     'alphabets': array<class-string, string>,
     *     'min_length': int,
     *     'min_lengths': array<class-string, int>,
     *     'key': string,
     *     'blocklist': array<int, string>,
     *     'canonical_checks': array<class-string, bool>
     * } $config
     *
     * @throws InvalidAlphabetException
     * @throws InvalidConfigException
     * @throws InvalidMinLengthException
     */
    public function __construct(array $config)
    {
        $this->resolveAlphabet($config);
        $this->resolveMinLength($config);
        $this->resolveKey($config);

        $this->resolveModelAlphabets($config);
        $this->resolveModelMinLengths($config);
        $this->resolveModelCanonicalChecks($config);
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): void
    {
        $this->key = str_replace('base64:', '', $key);
    }

    public function getAlphabet(): Alphabet
    {
        return $this->alphabet;
    }

    /**
     * @throws InvalidAlphabetException
     */
    public function setAlphabet(string|Alphabet $alphabet): Alphabet
    {
        if ($alphabet instanceof Alphabet) {
            return $this->alphabet = $alphabet;
        }

        return $this->alphabet = new Alphabet($alphabet);
    }

    public function getAlphabetFor(string|Model $model): ?Alphabet
    {
        if ($model instanceof Model) {
            return $this->modelAlphabets[$model::class] ?? null;
        }

        return $this->modelAlphabets[$model] ?? null;
    }

    public function setAlphabetFor(string|Model $model, Alphabet $alphabet): Alphabet
    {
        if ($model instanceof Model) {
            return $this->modelAlphabets[$model::class] = $alphabet;
        }

        return $this->modelAlphabets[$model] = $alphabet;
    }

    /** @return array<string, Alphabet> */
    public function getAlphabets(): array
    {
        return $this->modelAlphabets;
    }

    public function getMinLength(): MinLength
    {
        return $this->minLength;
    }

    /**
     * @throws InvalidMinLengthException
     */
    public function setMinLength(int|MinLength $minLength): MinLength
    {
        if ($minLength instanceof MinLength) {
            return $this->minLength = $minLength;
        }

        return $this->minLength = new MinLength($minLength);
    }

    public function getMinLengthFor(string|object $model): ?MinLength
    {
        if (is_string($model)) {
            return $this->modelMinLengths[$model] ?? null;
        }

        return $this->modelMinLengths[$model::class] ?? null;
    }

    public function setMinLengthFor(string|object $model, MinLength $minLength): MinLength
    {
        if (is_string($model)) {
            return $this->modelMinLengths[$model] = $minLength;
        }

        return $this->modelMinLengths[$model::class] = $minLength;
    }

    public function getCanonicalCheckFor(string|Model $model): ?bool
    {
        if ($model instanceof Model) {
            return $this->canonicalChecks[$model::class] ?? null;
        }

        return $this->canonicalChecks[$model] ?? null;
    }

    public function setCanonicalCheckFor(string|Model $model, bool $check): void
    {
        if ($model instanceof Model) {
            $this->canonicalChecks[$model::class] = $check;

            return;
        }

        $this->canonicalChecks[$model] = $check;
    }

    /**
     * @param  array<string, mixed>  $config
     *
     * @throws InvalidConfigException
     */
    protected function resolveKey(array $config): void
    {
        if (!isset($config['key'])) {
            throw new InvalidConfigException('a key must be set in the configuration');
        }

        if (!is_string($config['key'])) {
            throw new InvalidConfigException('the key set in `sqids.php` must be a string');
        }

        $this->key = str_replace('base64:', '', $config['key']);
    }

    /**
     * @param  array<string, mixed>  $config
     *
     * @throws InvalidAlphabetException
     */
    protected function resolveAlphabet(array $config): void
    {
        if (!isset($config['alphabet'])) {
            $this->alphabet = new Alphabet(self::DEFAULT_ALPHABET);

            return;
        }

        if (!is_string($config['alphabet'])) {
            throw new InvalidConfigException('the default alphabet set in `sqids.php` must be of type string, int given');
        }

        $this->alphabet = new Alphabet($config['alphabet']);
    }

    /**
     * @param  array<string, mixed>  $config
     *
     * @throws InvalidMinLengthException
     */
    protected function resolveMinLength(array $config): void
    {
        if (!isset($config['min_length'])) {
            $this->minLength = new MinLength(self::DEFAULT_MIN_LENGTH);

            return;
        }

        if (!is_int($config['min_length'])) {
            throw new InvalidConfigException('the min length set in `sqids.php` must be of type int, string given');
        }

        $this->minLength = new MinLength($config['min_length']);
    }

    /**
     * @param  array<string, mixed>  $config
     *
     * @throws InvalidConfigException
     * @throws InvalidAlphabetException
     */
    protected function resolveModelAlphabets(array $config): void
    {
        if (!isset($config['alphabets'])) {
            return;
        }

        if (!is_array($config['alphabets'])) {
            throw new InvalidConfigException('`sqids.alphabets` must be an array<class-string, string>');
        }

        foreach ($config['alphabets'] as $model => $alphabet) {
            if (!is_string($model)) {
                throw new InvalidConfigException('`sqids.alphabets` must be an array<class-string, string>');
            }

            $this->modelAlphabets[$model] = new Alphabet($alphabet);
        }
    }

    /**
     * @param  array<string, mixed>  $config
     *
     * @throws InvalidConfigException
     * @throws InvalidMinLengthException
     */
    protected function resolveModelMinLengths(array $config): void
    {
        if (!isset($config['min_lengths'])) {
            return;
        }

        if (!is_array($config['min_lengths'])) {
            throw new InvalidConfigException('`sqids.min_lengths` must be an array<class-string, int>');
        }

        foreach ($config['min_lengths'] as $model => $minLength) {
            if (!is_string($model)) {
                throw new InvalidConfigException('`sqids.min_lengths` must be an array<class-string, int>');
            }

            $this->modelMinLengths[$model] = new MinLength($minLength);
        }
    }

    /**
     * @param  array<string, mixed>  $config
     *
     * @throws InvalidConfigException
     */
    protected function resolveModelCanonicalChecks(array $config): void
    {
        if (!isset($config['canonical_checks'])) {
            return;
        }

        if (!is_array($config['canonical_checks'])) {
            throw new InvalidConfigException('`sqids.canonical_checks` must be an array<class-string, bool>');
        }

        foreach ($config['canonical_checks'] as $model => $check) {
            if (!is_string($model) || !is_bool($check)) {
                throw new InvalidConfigException('`sqids.canonical_checks` must be an array<class-string, bool>');
            }

            $this->canonicalChecks[$model] = $check;
        }
    }
}
