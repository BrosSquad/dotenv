<?php


namespace Dusan\PhpMvc\Env\Exceptions;


use Exception;
use Throwable;

class EnvVariableNotFound extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = NULL)
    {
        parent::__construct($message, $code, $previous);
    }
}
