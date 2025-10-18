<?php

namespace App\Service\Environment;

class Environment
{

    const ENV_PROD = 'prod';
    const ENV_DEV = 'dev';
    const ENV_TEST = 'test';

    public function __construct(private string $envName) {}

    public function isProduction(): bool
    {
        return $this->envName === self::ENV_PROD;
    }

    public function isDevelopment(): bool
    {
        return $this->envName === self::ENV_DEV;
    }

    public function isTest(): bool
    {
        return $this->envName === self::ENV_TEST;
    }

    public function get(): string
    {
        return $this->envName;
    }
}