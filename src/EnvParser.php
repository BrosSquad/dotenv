<?php

declare(strict_types=1);

namespace BrosSquad\DotEnv;

use BrosSquad\DotEnv\Exceptions\DotEnvSyntaxError;
use BrosSquad\DotEnv\Exceptions\EnvNotParsed;
use BrosSquad\DotEnv\Exceptions\EnvVariableNotFound;
use Dusan\PhpMvc\File\File;
use Exception;

class EnvParser implements Tokens, EnvParserInterface
{
    /**
     * ENV File handler
     *
     * @internal
     * @var File|NULL
     */
    private $handler;

    /**
     * Holder for parsed Environment Variables
     *
     * @internal
     * @var null|array
     */
    private $envs = NULL;

    /**
     * Flag that doesn't allows the lexing and paring stages to happen twice
     *
     * @var bool
     */
    private $isParsed = false;

    /**
     * EnvParser constructor.
     *
     * @param string $file
     *
     * @throws \Exception
     */
    public function __construct(string $file)
    {
        $this->handler = new File($file);
        if ($this->handler === NULL) {
            throw new Exception('File could not be opened');
        }
        if (!$this->handler->isFile()) {
            throw new Exception($file . ' is not a file');
        }
        if (!$this->handler->isReadable()) {
            throw new Exception($file . ' is not readable');
        }
    }


    /**
     * {@inheritDoc}
     *
     * @param bool $raw
     *
     * @return void
     * @throws DotEnvSyntaxError
     * @throws EnvVariableNotFound
     * @throws \Exception
     */
    public function parse(bool $raw = false): void
    {
        $this->handler->sharedLock();

        $envs = [];

        while (($c = $this->handler->fgetc()) !== false) {
            $column = 0;
            // Handling Comments, Empty lines and leading spaces
            if ($c === self::COMMENT) while (($c = $this->handler->fgetc()) !== self::NEW_LINE) continue;
            if ($c === self::NEW_LINE || $c === self::CARRIAGE_RETURN || $c === self::SPACE) continue;
            $envs[$this->extractName($c, $column)] = $this->extractValue($envs, $raw, $column);
        }

        $this->envs = $envs;
        $this->isParsed = true;
        $this->handler->unlock();
    }

    /**
     * @param string $startingChar
     * @param int    $column
     *
     * @return string
     * @throws \BrosSquad\DotEnv\Exceptions\DotEnvSyntaxError
     */
    private function extractName(string $startingChar, int & $column): string
    {
        $key = $startingChar;
        while (($c = $this->handler->fgetc()) !== self::EQUALS) {
            // Ignoring every white space
            if ($c === self::SPACE) {
                while (($c = $this->handler->fgetc()) === self::SPACE) {
                    continue;
                }
                if ($c === self::EQUALS) break;
                else {
                    $error = new DotEnvSyntaxError('Spaces are now allowed in env variable name');
                    $error->setEnvLine($this->handler->key());
                    $error->setColumn($column);
                    throw $error;
                }
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
     * @param array $envs
     * @param bool  $raw
     * @param int   $column
     *
     * @return string
     * @throws \BrosSquad\DotEnv\Exceptions\EnvVariableNotFound
     */
    private function extractValue(array $envs, bool $raw, int & $column): string
    {
        $value = '';
        // Trimming the leading spaces of the value
        while (($c = $this->handler->fgetc()) === self::SPACE) {
            $column++;
            continue;
        };
        $this->handler->fseek($this->handler->ftell() - 1);

        // Handling Multiline values
        if ($c === self::MULTI_LINE_START) {
            $this->handler->fseek($this->handler->ftell() + 1);
            while (($c = $this->handler->fgetc()) !== false && $c !== self::MULTI_LINE_STOP) {
                // Handle the interpolation
                if ($c === self::INTERPOLATION_INDICATOR && ($c = $this->handler->fgetc()) === self::INTERPOLATION_START && !$raw) {
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
            if ($c === self::CARRIAGE_RETURN) break;
            if ($c === self::NEW_LINE) break;
            // Every space character will be ignored
            if ($c === self::SPACE) break;
            // If comment is found at the end of value it will be ignored
            if ($c === self::COMMENT)
                // Just moving the file pointer to the or \n
                while (($c = $this->handler->fgetc()) !== false && $c !== self::NEW_LINE) {
                    $column++;
                    continue;
                }
            else
                $value .= $c;
            $column++;
        }
        return $value;
    }

    /**
     * @param array $envs
     *
     * @return mixed
     * @throws \BrosSquad\DotEnv\Exceptions\EnvVariableNotFound
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
     * @return void
     * @throws \BrosSquad\DotEnv\Exceptions\EnvNotParsed
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
     * @return void
     * @throws \BrosSquad\DotEnv\Exceptions\EnvNotParsed
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
        $this->handler = NULL;
    }
}
