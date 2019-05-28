<?php

namespace Dusan\PhpMvc\Env;

if(!function_exists('env')) {
    function env(string $name) {
        return getenv($name);
    }
}
