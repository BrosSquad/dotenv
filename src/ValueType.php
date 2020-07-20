<?php


namespace BrosSquad\DotEnv;


interface ValueType
{
    public const NULL = 0;
    public const INTEGER = 1;
    public const FLOAT = 2;
    public const STRING = 'STRING';
    public const BOOLEAN = 'BOOLEAN';

    public function detectValue(string $value, bool $shouldBeString);
}
