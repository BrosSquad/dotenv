<?php


namespace BrosSquad\DotEnv\Exceptions;


use Exception;
use Throwable;

class DotEnvSyntaxError extends Exception
{
    /** @var integer */
    private $envLine;

    /** @var integer */
    private $column;

    public function __construct($message = 'Check your syntax in .env file', $code = 0, Throwable $previous = NULL)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return int
     */
    public function getEnvLine(): int
    {
        return $this->envLine;
    }

    /**
     * @param int $envLine
     *
     * @return DotEnvSyntaxError
     */
    public function setEnvLine(int $envLine): DotEnvSyntaxError
    {
        $this->envLine = $envLine;
        return $this;
    }


    /**
     * @return int
     */
    public function getColumn(): int
    {
        return $this->column;
    }

    /**
     * @param int $column
     *
     * @return DotEnvSyntaxError
     */
    public function setColumn(int $column): DotEnvSyntaxError
    {
        $this->column = $column;
        return $this;
    }


}
