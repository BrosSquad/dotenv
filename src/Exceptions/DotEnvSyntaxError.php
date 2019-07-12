<?php


namespace Dusan\DotEnv\Exceptions;


use Exception;
use Throwable;

class DotEnvSyntaxError extends Exception
{
    public function __construct($message = "Check your syntax in .env file", $code = 0, Throwable $previous = NULL)
    {
        parent::__construct($message, $code, $previous);
    }
}
