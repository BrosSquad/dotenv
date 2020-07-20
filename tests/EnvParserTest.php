<?php


namespace BrosSquad\DotEnv\Tests;


use Exception;
use Throwable;
use BrosSquad\DotEnv\EnvParser;
use PHPUnit\Framework\TestCase;
use BrosSquad\DotEnv\Exceptions\DotEnvSyntaxError;
use BrosSquad\DotEnv\Exceptions\EnvVariableNotFound;

class EnvParserTest extends TestCase
{

    public function test_env_parser(): void
    {
        try {
            $parser = new EnvParser(__DIR__.'/.env');
            $parser->parse(true);
            $array = $parser->getEnvs();
            self::assertIsArray($array);
            self::assertArrayHasKey('APP_NAME', $array);
            self::assertArrayHasKey('DB_NAME', $array);
            self::assertArrayHasKey('MULTI_LINE', $array);
            self::assertArrayHasKey('COMMENTS_AT_END_OF_VALUE', $array);
            self::assertArrayHasKey('EMPTY_ENV', $array);
            self::assertArrayHasKey('NAME_AND_VAlUE', $array);
            self::assertEquals('Test', $array['APP_NAME']);
            self::assertEquals('test', $array['DB_NAME']);
            self::assertEquals('', $array['EMPTY_ENV']);
            self::assertEquals(null, $array['NULL_ENV']);
            self::assertEquals('Name', $array['COMMENTS_AT_END_OF_VALUE']);
            self::assertEquals(
                ' this
              is multi line
',
                $array['MULTI_LINE']
            );
        } catch (DotEnvSyntaxError $e) {
            self::fail(sprintf('%s %d %d', $e->getMessage(), $e->getEnvLine(), $e->getColumn()));
        } catch (Exception $e) {
            self::fail($e->getMessage());
        }
    }

    /**
     * @throws DotEnvSyntaxError
     * @throws EnvVariableNotFound
     * @throws Exception
     */
    public function test_env_with_error(): void
    {
        $this->expectException(DotEnvSyntaxError::class);
        $parser = new EnvParser(__DIR__.'/.env-error');
        $parser->parse();
    }

    /**
     * @throws DotEnvSyntaxError
     * @throws EnvVariableNotFound
     * @throws Exception
     */
    public function test_env_with_space_error_in_variable_name(): void
    {
        $this->expectException(DotEnvSyntaxError::class);
        $parser = new EnvParser(__DIR__.'/.env-error');
        $parser->parse();
    }

    public function test_interpolation(): void
    {
        $expected = 'Test is Interpolated';
        try {
            $parser = new EnvParser(__DIR__.'/.env.interpolation');
            $parser->parse();
            $envs = $parser->getEnvs();
            self::assertIsArray($envs);
            self::assertArrayHasKey('INTERPOLATION', $envs);
            self::assertEquals($expected, $envs['INTERPOLATION']);
        } catch (Exception $e) {
            self::fail($e->getMessage());
        }
    }

    public function test_casting(): void
    {
        try {
            $parser = new EnvParser(__DIR__.'/.env.casts');
            $parser->parse();
            $envs = $parser->getEnvs();
            self::assertIsArray($envs);

            self::assertIsInt($envs['INTEGERS']);
            self::assertIsInt($envs['INT_ZERO']);
            self::assertIsBool($envs['BOOLEAN']);
            self::assertIsString($envs['STR']);
            self::assertIsFloat($envs['FLOATS']);
            self::assertIsFloat($envs['FLOAT_ZERO']);
            self::assertNull($envs['NULLABLE']);
            self::assertNull($envs['UPPERNULL']);
            self::assertEquals(123, $envs['INTEGERS']);
            self::assertEquals(0, $envs['INT_ZERO']);
            self::assertEquals(false, $envs['BOOLEAN']);
            self::assertEquals(true, $envs['BOOLEAN_TRUE']);
            self::assertEquals(1.65, $envs['FLOATS']);
            self::assertEquals(0.0, $envs['FLOAT_ZERO']);
        } catch (Exception $e) {
            self::fail($e->getMessage());
        }
    }

    public function test_empty_value(): void
    {
        try {
            $parser = new EnvParser(__DIR__.'/.env.empty.value');
            $parser->parse();
            $envs = $parser->getEnvs();
            self::assertIsArray($envs);
            self::assertArrayHasKey('APP_NAME', $envs);
            self::assertArrayHasKey('DB_NAME', $envs);
            self::assertArrayHasKey('TEST_EMPTY', $envs);
            self::assertEquals('', $envs['TEST_EMPTY']);
        } catch (Throwable $e) {
            self::fail($e->getMessage());
        }
    }
}
