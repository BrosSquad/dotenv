<?php


namespace Dusan\PhpMvc\Env;


interface SetFile
{

    /**
     * Sets the location to the .env file
     * @param string $file
     * @return void
     */
    public function setFile(string $file): void;

}
