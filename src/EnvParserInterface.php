<?php

declare(strict_types=1);

namespace BrosSquad\DotEnv;


use BrosSquad\DotEnv\Exceptions\DotEnvSyntaxError;
use BrosSquad\DotEnv\Exceptions\EnvVariableNotFound;

interface EnvParserInterface
{
    /**
     * Parses the File loaded trough the constructor
     * Every key is returned as
     *
     * @param bool $raw
     *
     * @return void
     */
    public function parse(bool $raw = false): void;

    /**
     * Loads ENVs into $_ENV Super global variable
     *
     * @return void
     */
    public function loadIntoENV(): void;

    public function getEnvs(): array;

    /**
     * Loads all ENVs using putenv() function
     *
     * @return void
     */
    public function loadUsingPutEnv(): void;

    /**
     * @param string $envName
     * @param string $value
     * @param bool $shouldQuote
     * @return int
     * @throws DotEnvSyntaxError
     * @throws EnvVariableNotFound
     */
    public function write(string $envName, string $value, bool $shouldQuote = false): int;
}
