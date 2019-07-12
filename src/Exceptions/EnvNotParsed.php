<?php


namespace BrosSquad\DotEnv\Exceptions;


use Exception;
use Throwable;

class EnvNotParsed extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = NULL)
    {
        parent::__construct($message, $code, $previous);
    }
}
