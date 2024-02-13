<?php

declare(strict_types=1);

namespace RossBearman\Sqids;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RossBearman\Sqids\Codecs\SqidCodec;
use RossBearman\Sqids\Exceptions\InvalidAlphabetException;
use RossBearman\Sqids\Exceptions\InvalidMinLengthException;
use RossBearman\Sqids\Support\Alphabet;
use RossBearman\Sqids\Support\ConfigResolver;
use RossBearman\Sqids\Support\MinLength;

final class Sqids
{
    protected ConfigResolver $config;

    /** @var array<string, SqidCodec> */
    protected array $modelSqids = [];

    public function __construct(ConfigResolver $config)
    {
        $this->config = $config;
    }

    public function sqidForModel(Model $model): string
    {
        if (!is_int($model->getKey())) {
            throw new InvalidArgumentException('Sqids are only compatible with integer IDs');
        }

        return $this->fromClass($model::class)->encode($model->getKey());
    }

    public function fromModel(Model $model): SqidCodec
    {
        return $this->fromClass($model::class);
    }

    public function fromClass(string $class): SqidCodec
    {
        $class = trim($class, '\\');

        if (isset($this->modelSqids[$class])) {
            return $this->modelSqids[$class];
        }

        $alphabet = $this->config->getAlphabetFor($class) ??
                    $this->config->setAlphabetFor($class, $this->shuffleAlphabetFor($class));

        $minLength = $this->config->getMinLengthFor($class) ?? $this->getDefaultMinLength();

        $canonicalCheck = $this->config->getCanonicalCheckFor($class) ?? true;

        return $this->setModel($class, $alphabet, $minLength, $canonicalCheck);
    }

    public function setModel(string $class, Alphabet $alphabet, MinLength $minLength, bool $canonicalCheck = true): SqidCodec
    {
        return $this->modelSqids[$class] = new SqidCodec($alphabet, $minLength, $canonicalCheck);
    }

    public function shuffleAlphabetFor(string $model): Alphabet
    {
        $modelClass = Str::afterLast($model, '\\');

        return $this->shuffleDefaultAlphabet($modelClass . '_' . $this->getKey());
    }

    public function shuffleDefaultAlphabet(string $seed): Alphabet
    {
        return $this->getDefaultAlphabet()->shuffle($seed);
    }

    public function getDefaultAlphabet(): Alphabet
    {
        return $this->config->getAlphabet();
    }

    /**
     * @throws InvalidAlphabetException
     */
    public function setDefaultAlphabet(string|Alphabet $alphabet): void
    {
        $this->config->setAlphabet($alphabet);
    }

    public function getDefaultMinLength(): MinLength
    {
        return $this->config->getMinLength();
    }

    /**
     * @throws InvalidMinLengthException
     */
    public function setDefaultMinLength(int $defaultMinLength): void
    {
        $this->config->setMinLength($defaultMinLength);
    }

    public function getKey(): string
    {
        return $this->config->getKey();
    }

    public function setKey(string $key): void
    {
        $this->config->setKey($key);
    }
}
