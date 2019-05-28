<?php


namespace Dusan\PhpMvc\Env\Impl;


use Dusan\PhpMvc\Env\EnvParser;
use Dusan\PhpMvc\Env\Exceptions\DotEnvSyntaxError;
use Dusan\PhpMvc\Env\Exceptions\EnvNotParsed;
use Dusan\PhpMvc\Env\Exceptions\EnvVariableNotFound;
use Dusan\PhpMvc\Env\Tokens;
use Dusan\PhpMvc\Exceptions\FileNotFoundException;
use Dusan\PhpMvc\Exceptions\NullPointerException;
use Dusan\PhpMvc\Storage\File;
use Exception;

class EnvParserImpl implements Tokens, EnvParser
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
     * Flag that doesn't allows the paring stage to happen twice
     *
     * @var bool
     */
    private $isParsed = false;

    /**
     * @param string $file
     *
     * @inheritDoc
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function setFile(string $file): void
    {
        $this->handler = new File($file);
        if ($this->handler === NULL) {
            throw new FileNotFoundException();
        }
        if (!$this->handler->isFile()) {
            throw new Exception($file . ' is not a file');
        }
        if (!$this->handler->isReadable()) {
            throw new Exception($file . ' is not readable');
        }

    }

    /**
     * @inheritDoc
     *
     * @param bool $raw
     *
     * @return void
     * @throws DotEnvSyntaxError
     * @throws EnvVariableNotFound
     * @throws \Dusan\PhpMvc\Exceptions\NullPointerException
     * @throws \Exception
     */
    public function parse(bool $raw = false): void
    {
        if (is_null($this->handler)) {
            throw new NullPointerException('setFile method must be called before the parse');
        }
        $this->handler->sharedLock();

        $envs = [];
        while(($c = $this->handler->fgetc()) !== false) {
            // Handling Comments, Empty lines and leading spaces
            if ($c === self::COMMENT) while (($c = $this->handler->fgetc()) !== self::NEW_LINE) continue;
            if ($c === self::NEW_LINE || $c === self::CARRIAGE_RETURN || $c === self::SPACE) continue;
            $envs[$this->extractName($c)] = $this->extractValue($envs, $raw);
        }

        $this->envs = $envs;
        $this->isParsed = true;
        $this->handler->unlock();
    }

    /**
     * @param string $startingChar
     *
     * @return string
     * @throws \Dusan\PhpMvc\Env\Exceptions\DotEnvSyntaxError
     */
    private function extractName(string $startingChar): string
    {
        $key = $startingChar;
        while (($c = $this->handler->fgetc()) !== self::EQUALS) {
            // Ignoring every white space
            if ($c === self::SPACE) continue;
            if ($c === self::CARRIAGE_RETURN || $c === self::NEW_LINE || $c === self::COMMENT)
                throw new DotEnvSyntaxError('Error on line ' . $this->handler->key());
            $key .= $c;
        }
        return $key;
    }

    /**
     * @param array $envs
     * @param bool  $raw
     *
     * @return string
     * @throws \Dusan\PhpMvc\Env\Exceptions\EnvVariableNotFound
     */
    private function extractValue(array $envs, bool $raw): string
    {
        $value = '';
        // Trimming the leading spaces of the value
        while (($c = $this->handler->fgetc()) === self::SPACE) continue;
        // Handling Multiline values
        if ($c === self::MULTI_LINE_START) {
            while (($c = $this->handler->fgetc()) !== false && $c !== self::MULTI_LINE_STOP) {
                // Handle the interpolation
                if ($c === self::INTERPOLATION_INDICATOR && ($c = $this->handler->fgetc()) === self::INTERPOLATION_START && !$raw) {
                    $value .= $this->interpolation($envs);
                } else {
                    $value .= $c;
                }
            }
        } else {
            // Current value of $c must be appended first in non multiline value
            $value .= $c;

            // Single line values in env
            while (($c = $this->handler->fgetc()) !== false && $c !== self::CARRIAGE_RETURN && $c !== self::NEW_LINE) {
                // Every space character will be ignored
                if ($c === self::SPACE) continue;
                // If comment is found at the end of value it will be ignored
                if ($c === self::COMMENT)
                    // Just moving the file pointer to the \r or \n
                    while (($c = $this->handler->fgetc()) !== false && $c !== self::CARRIAGE_RETURN && $c !== self::NEW_LINE) continue;
                // Appending the read value to the temporary handler
                $value .= $c;
            }
        }
        return $value;
    }

    /**
     * @param array $envs
     *
     * @return mixed
     * @throws \Dusan\PhpMvc\Env\Exceptions\EnvVariableNotFound
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
     * @throws \Dusan\PhpMvc\Env\Exceptions\EnvNotParsed
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
     * @throws \Dusan\PhpMvc\Env\Exceptions\EnvNotParsed
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
