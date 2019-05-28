<?php


namespace Dusan\PhpMvc\Env;


use Dusan\PhpMvc\Env\Impl\EnvParserImpl;
use Dusan\PhpMvc\ServiceProvider\ServiceProvider;

class EnvServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->make(EnvParser::class, EnvParserImpl::class);
    }
}
