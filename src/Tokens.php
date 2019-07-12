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
    const EQUALS = '=';
    const COMMENT = '#';
    const MULTI_LINE_START = '"';
    const MULTI_LINE_STOP = '"';
    const SPACE = ' ';
    const NEW_LINE = "\n";
    const TAB = "\t";
    const CARRIAGE_RETURN = "\r";

    const INTERPOLATION_INDICATOR = '$';
    const INTERPOLATION_START = '{';
    const INTERPOLATION_END = '}';
}
