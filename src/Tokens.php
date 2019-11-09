<?php

declare(strict_types=1);

namespace BrosSquad\DotEnv;

/**
 * ENV Tokens
 * This interfaces holds the tokens used in lexical process
 *
 * @package BrosSquad\DotEnv
 */
interface Tokens
{
    public const EQUALS = '=';
    public const COMMENT = '#';
    public const MULTI_LINE_START = '"';
    public const MULTI_LINE_STOP = '"';
    public const SPACE = ' ';
    public const NEW_LINE = "\n";
    public const TAB = "\t";
    public const CARRIAGE_RETURN = "\r";

    public const INTERPOLATION_INDICATOR = '$';
    public const INTERPOLATION_START = '{';
    public const INTERPOLATION_END = '}';
}
