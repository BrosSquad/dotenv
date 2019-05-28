<?php


namespace Dusan\PhpMvc\Tests\Env;


use Dusan\PhpMvc\Env\Exceptions\DotEnvSyntaxError;
use Dusan\PhpMvc\Env\Impl\EnvParserImpl;
use Dusan\PhpMvc\Exceptions\NullPointerException;
use Dusan\PhpMvc\Tests\PhpMvcTestCase;
use Exception;

class EnvParserTest extends PhpMvcTestCase
{
    public function test_env_parser() {

        try {
            $parser = new EnvParserImpl();
            $parser->setFile(__DIR__ . '/.env');
            $parser->parse(true);
            $array = $parser->getEnvs();
            $this->assertIsArray($array);
            $this->assertArrayHasKey('APP_NAME', $array);
            $this->assertArrayHasKey('DB_NAME', $array);
            $this->assertArrayHasKey('MULTI_LINE', $array);
            $this->assertArrayHasKey('COMMENTS_AT_END_OF_VALUE', $array);
            $this->assertArrayHasKey('NAMEANDVAlUE', $array);
            $this->assertEquals('Test', $array['APP_NAME']);
            $this->assertEquals('test', $array['DB_NAME']);
            $this->assertEquals('Name', $array['COMMENTS_AT_END_OF_VALUE']);
            $this->assertEquals(' this
              is multi line
', $array['MULTI_LINE']);
        } catch (DotEnvSyntaxError $e) {
            $this->fail($e->getMessage());
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function test_env_with_error() {
        $this->expectException(DotEnvSyntaxError::class);
        $parser = new EnvParserImpl();
        $parser->setFile(__DIR__ . '/.env-error');
        $parser->parse();
    }

    public function test_not_calling_setFile_method() {
        $this->expectException(NullPointerException::class);
        $this->expectExceptionMessage('setFile method must be called before the parse');
        $parser = new EnvParserImpl();
        $parser->parse();
    }

    public function test_interpolation() {
        $expected = 'Test is Interpolated';
        try {
            $parser = new EnvParserImpl();
            $parser->setFile(__DIR__ . '/.env.interpolation');
            $parser->parse();
            $envs = $parser->getEnvs();
            $this->assertIsArray($envs);
            $this->assertArrayHasKey('INTERPOLATION', $envs);
            $this->assertEquals($expected, $envs['INTERPOLATION']);
        }catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
