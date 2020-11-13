<?php

declare(strict_types=1);

namespace BrosSquad\DotEnv;

use Exception;
use RuntimeException;
use BrosSquad\DotEnv\Exceptions\EnvNotParsed;
use BrosSquad\DotEnv\Exceptions\DotEnvSyntaxError;
use BrosSquad\DotEnv\Exceptions\EnvVariableNotFound;

class EnvParser implements Tokens, EnvParserInterface
{
    /**
     * ENV File handler
     *
     * @internal
     * @var File|NULL
     */
    private ?File $handler;

    /**
     * @var ValueType
     */
    private ValueType $typeChecker;

    /**
     * Holder for parsed Environment Variables
     *
     * @internal
     * @var null|array
     */
    private ?array $envs;

    /**
     * Flag that doesn't allows the lexing and paring stages to happen twice
     *
     * @var bool
     */
    private bool $isParsed = false;

    /**
     * EnvParser constructor.
     *
     * @param  string  $file
     *
     * @param  ValueType|null  $typeChecker
     * @param  bool  $emptyStringNull
     */
    public function __construct(
        string $file,
        ValueType $typeChecker = null,
        bool $emptyStringNull = true
    ) {
        $this->handler = new File($file);
        if ($this->handler === null) {
            throw new RuntimeException('File could not be opened');
        }
        if (!$this->handler->isFile()) {
            throw new RuntimeException($file . ' is not a file');
        }
        if (!$this->handler->isReadable()) {
            throw new RuntimeException($file . ' is not readable');
        }

        if ($typeChecker === null) {
            $typeChecker = new TypeChecker($emptyStringNull);
        }

        $this->typeChecker = $typeChecker;
    }


    /**
     * {@inheritDoc}
     *
     * @throws DotEnvSyntaxError
     * @throws EnvVariableNotFound
     * @throws Exception
     *
     * @param  bool  $raw
     *
     * @return void
     */
    public function parse(bool $raw = false): void
    {
        $this->handler->sharedLock();

        $envs = [];

        while (($c = $this->handler->fgetc()) !== false) {
            $column = 0;
            // Handling Comments, Empty lines and leading spaces
            if ($c === self::COMMENT) {
                while (($c = $this->handler->fgetc()) !== self::NEW_LINE) {
                    continue;
                }
            }
            if ($c === self::NEW_LINE || $c === self::CARRIAGE_RETURN || $c === self::SPACE) {
                continue;
            }
            $envs[$this->extractName($c, $column)] = $this->extractValue($envs, $raw, $column);
        }

        $this->envs = $envs;
        $this->isParsed = true;
        $this->handler->unlock();
    }

    /**
     * @throws DotEnvSyntaxError
     *
     * @param  int  $column
     *
     * @param  string  $startingChar
     *
     * @return string
     */
    private function extractName(string $startingChar, int &$column): string
    {
        $key = $startingChar;
        while (($c = $this->handler->fgetc()) !== self::EQUALS) {
            // Ignoring every white space
            if ($c === self::SPACE) {
                while (($c = $this->handler->fgetc()) === self::SPACE) {
                    continue;
                }
                if ($c === self::EQUALS) {
                    break;
                }

                $error = new DotEnvSyntaxError('Spaces are now allowed in env variable name');
                $error->setEnvLine($this->handler->key());
                $error->setColumn($column);
                throw $error;
            }
            if ($c === self::CARRIAGE_RETURN || $c === self::NEW_LINE || $c === self::COMMENT) {
                $error = new DotEnvSyntaxError('Unexpected end of line');
                $error->setEnvLine($this->handler->key());
                $error->setColumn($column);
                throw $error;
            }
            $key .= $c;
            $column++;
        }
        return $key;
    }

    /**
     * Parses the individual value from the .env file
     *
     * @throws EnvVariableNotFound
     *
     * @param  bool  $raw
     * @param  int  $column
     *
     * @param  array  $envs
     *
     * @return int|string|float|null
     */
    private function extractValue(array $envs, bool $raw, int &$column)
    {
        $value = '';
        $shouldBeString = false;
        // Trimming the leading spaces of the value
        while (($c = $this->handler->fgetc()) === self::SPACE) {
            $column++;
            continue;
        }

        $this->handler->fseek($this->handler->ftell() - 1);

        // Handling Multiline values
        if ($c === self::MULTI_LINE_START) {
            $shouldBeString = true;
            $this->handler->fseek($this->handler->ftell() + 1);
            while (($c = $this->handler->fgetc()) !== false && $c !== self::MULTI_LINE_STOP) {
                // Handle the interpolation
                if (
                    !$raw &&
                    $c === self::INTERPOLATION_INDICATOR &&
                    ($c = $this->handler->fgetc()) === self::INTERPOLATION_START
                ) {
                    $value .= $this->interpolation($envs);
                } else {
                    $value .= $c;
                }
                $column++;
            }
            return $value;
        }
        // Handling Single line values
        while (($c = $this->handler->fgetc()) !== false) {
            if ($c === self::CARRIAGE_RETURN) {
                break;
            }
            if ($c === self::NEW_LINE) {
                break;
            }
            // Every space character will be ignored
            if ($c === self::SPACE) {
                break;
            }
            // If comment is found at the end of value it will be ignored
            if ($c === self::COMMENT) {
                // Just moving the file pointer to the or \n
                while (($c = $this->handler->fgetc()) !== false && $c !== self::NEW_LINE) {
                    $column++;
                    continue;
                }
            } else {
                $value .= $c;
            }
            $column++;
        }

        return $this->typeChecker->detectValue($value, $shouldBeString);
    }

    /**
     * @throws EnvVariableNotFound
     *
     * @param  array  $envs
     *
     * @return mixed
     */
    private function interpolation(array $envs)
    {
        $tmp = '';
        while (($c = $this->handler->fgetc()) !== self::INTERPOLATION_END) {
            $tmp .= $c;
        }
        if (!isset($envs[$tmp])) {
            throw new EnvVariableNotFound($tmp . ' is not found');
        }
        return $envs[$tmp];
    }

    /**
     * Loads ENVs into $_ENV Super global variable
     *
     * @throws EnvNotParsed
     * @return void
     */
    public function loadIntoENV(): void
    {
        if (!$this->isParsed) {
            throw new EnvNotParsed('Parse method must be called before loadIntoENV() method');
        }

        foreach ($this->envs as $key => $value) {
            $_ENV[$key] = $value;
        }
    }

    /**
     * Loads all ENVs using putenv() function
     *
     * @inheritDoc
     * @throws EnvNotParsed
     * @return void
     */
    public function loadUsingPutEnv(): void
    {
        if (!$this->isParsed) {
            throw new EnvNotParsed('Parse method must be called before loadIntoENV() method');
        }

        foreach ($this->envs as $key => $value) {
            putenv("{$key}={$value}");
        }
    }

    public function getEnvs(): array
    {
        return $this->envs;
    }

    public function __destruct()
    {
        unset($this->handler);
        $this->handler = null;
    }

    /**
     * @throws DotEnvSyntaxError
     * @throws EnvVariableNotFound
     *
     * @param  bool  $shouldQuote
     * @param  string  $envName
     * @param  string  $value
     *
     * @return int
     */
    public function write(string $envName, string $value, bool $shouldQuote = false): int
    {
        if (!$this->isParsed) {
            $this->parse();
        }
        if ($shouldQuote === true) {
            $value = '"' . $value . '"';
        }

        $this->envs[$envName] = $value;

        return $this->handler->writeArray($this->envs);
    }
}
