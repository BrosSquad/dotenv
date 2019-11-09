<?php


namespace BrosSquad\DotEnv;


interface Parser
{
    /**
     * @param string $value
     * @return mixed
     */
    public function parse(string $value);
}