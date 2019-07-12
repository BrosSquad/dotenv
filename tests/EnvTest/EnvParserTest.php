<?php


namespace Dusan\DotEnv\Tests;


use Dusan\DotEnv\Exceptions\DotEnvSyntaxError;
use Dusan\DotEnv\EnvParser;
use Exception;
use PHPUnit\Framework\TestCase;

class EnvParserTest extends TestCase
{
    public function test_env_parser()
    {

        try {
            $parser = new EnvParser(__DIR__ . '/.env');
            $parser->parse(true);
            $array = $parser->getEnvs();
            $this->assertIsArray($array);
            $this->assertArrayHasKey('APP_NAME', $array);
            $this->assertArrayHasKey('DB_NAME', $array);
            $this->assertArrayHasKey('MULTI_LINE', $array);
            $this->assertArrayHasKey('COMMENTS_AT_END_OF_VALUE', $array);
            $this->assertArrayHasKey('EMPTY_ENV', $array);
            $this->assertArrayHasKey('NAME_AND_VAlUE', $array);
            $this->assertEquals('Test', $array['APP_NAME']);
            $this->assertEquals('test', $array['DB_NAME']);
            $this->assertEquals('', $array['EMPTY_ENV']);
            $this->assertEquals('Name', $array['COMMENTS_AT_END_OF_VALUE']);
            $this->assertEquals(' this
              is multi line
', $array['MULTI_LINE']);
        } catch (DotEnvSyntaxError $e) {
            $this->fail(sprintf("%s %d %d", $e->getMessage(), $e->getEnvLine(), $e->getColumn()));
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function test_env_with_error()
    {
        $this->expectException(DotEnvSyntaxError::class);
        $parser = new EnvParser(__DIR__ . '/.env-error');
        $parser->parse();
    }

    public function test_env_with_space_error_in_variable_name()
    {
        $this->expectException(DotEnvSyntaxError::class);
        $parser = new EnvParser(__DIR__ . '/.env-error');
        $parser->parse();
    }

    public function test_interpolation()
    {
        $expected = 'Test is Interpolated';
        try {
            $parser = new EnvParser(__DIR__ . '/.env.interpolation');
            $parser->parse();
            $envs = $parser->getEnvs();
            $this->assertIsArray($envs);
            $this->assertArrayHasKey('INTERPOLATION', $envs);
            $this->assertEquals($expected, $envs['INTERPOLATION']);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
