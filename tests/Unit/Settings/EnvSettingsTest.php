<?php

declare(strict_types=1);

namespace Tests\Unit\Settings;

use Dotenv\Exception\ValidationException;
use kissj\Middleware\AddCorsHeaderForAppDomainsMiddleware;
use kissj\Settings\EnvSettings;
use LogicException;
use Monolog\Level;
use PHPUnit\Framework\TestCase;

class EnvSettingsTest extends TestCase
{
    /**
     * @return array<string, string>
     */
    private function validEnv(): array
    {
        return [
            'APP_NAME' => 'KISSJ',
            'BASEPATH' => '/kissj',
            'DEBUG' => 'false',
            'TEMPLATE_CACHE' => 'true',
            'SKAUTIS_USE_TEST' => 'true',
            'DEFAULT_LOCALE' => 'cs',
            'LOGGER_LEVEL' => 'WARNING',
            'MAIL_DSN' => 'smtp://mail:1025',
            'FILE_HANDLER_TYPE' => 'local',
            'S3_BUCKET' => 'bucket',
            'S3_KEY' => 'key',
            'S3_SECRET' => 'secret',
            'S3_REGION' => 'region',
            'S3_ENDPOINT' => 'https://s3.example.org',
            'DB_TYPE' => 'postgresql',
            'DATABASE_PATH' => '/tmp/db.sqlite',
            'DATABASE_HOST' => 'db',
            'POSTGRES_USER' => 'user',
            'POSTGRES_PASSWORD' => 'password',
            'POSTGRES_DB' => 'kissj',
            'SENTRY_DSN' => 'https://sentry.example.org/1',
            'SENTRY_TRACES_SAMPLE_RATE' => '0.25',
            'SENTRY_PROFILES_SAMPLE_RATE' => '0.5',
            'GIT_HASH' => 'abc123',
            'REDIS_HOST' => 'redis',
            'REDIS_PORT' => '6379',
            'REDIS_PASSWORD' => 'redispass',
            'CORS_ALLOWED_ORIGINS' => 'https://a.example.org,https://b.example.org',
        ];
    }

    public function testMapsValidEnvToTypedValues(): void
    {
        $env = EnvSettings::fromArray($this->validEnv(), '.env');

        self::assertSame('KISSJ', $env->appName);
        self::assertSame('/kissj', $env->basePath);
        self::assertFalse($env->debug);
        self::assertTrue($env->templateCache);
        self::assertTrue($env->skautisUseTest);
        self::assertSame('cs', $env->defaultLocale);
        self::assertSame(Level::Warning, $env->loggerLevel);
        self::assertSame('smtp://mail:1025', $env->mailDsn);
        self::assertSame('local', $env->fileHandlerType);
        self::assertSame('bucket', $env->s3Bucket);
        self::assertSame('key', $env->s3Key);
        self::assertSame('secret', $env->s3Secret);
        self::assertSame('region', $env->s3Region);
        self::assertSame('https://s3.example.org', $env->s3Endpoint);
        self::assertSame('postgresql', $env->dbType);
        self::assertSame('/tmp/db.sqlite', $env->databasePath);
        self::assertSame('db', $env->databaseHost);
        self::assertSame('user', $env->postgresUser);
        self::assertSame('password', $env->postgresPassword);
        self::assertSame('kissj', $env->postgresDb);
        self::assertSame('https://sentry.example.org/1', $env->sentryDsn);
        self::assertSame(0.25, $env->sentryTracesSampleRate);
        self::assertSame(0.5, $env->sentryProfilesSampleRate);
        self::assertSame('abc123', $env->gitHash);
        self::assertSame('redis', $env->redisHost);
        self::assertSame(6379, $env->redisPort);
        self::assertSame('redispass', $env->redisPassword);
        self::assertSame(['https://a.example.org', 'https://b.example.org'], $env->corsAllowedOrigins);
    }

    public function testBoolsKeepExactStringSemantics(): void
    {
        $env = EnvSettings::fromArray([
            'DEBUG' => '1',
            'TEMPLATE_CACHE' => '0',
            'SKAUTIS_USE_TEST' => 'no',
        ] + $this->validEnv(), '.env');

        self::assertFalse($env->debug);
        self::assertTrue($env->templateCache);
        self::assertTrue($env->skautisUseTest);

        $env = EnvSettings::fromArray([
            'DEBUG' => 'true',
            'TEMPLATE_CACHE' => 'false',
            'SKAUTIS_USE_TEST' => 'false',
        ] + $this->validEnv(), '.env');

        self::assertTrue($env->debug);
        self::assertFalse($env->templateCache);
        self::assertFalse($env->skautisUseTest);
    }

    public function testOptionalValuesFallBackToDefaults(): void
    {
        $values = $this->validEnv();
        unset(
            $values['SENTRY_TRACES_SAMPLE_RATE'],
            $values['SENTRY_PROFILES_SAMPLE_RATE'],
            $values['DB_TYPE'],
            $values['CORS_ALLOWED_ORIGINS'],
        );

        $env = EnvSettings::fromArray($values, '.env');

        self::assertSame(1.0, $env->sentryTracesSampleRate);
        self::assertSame(1.0, $env->sentryProfilesSampleRate);
        self::assertSame('postgresql', $env->dbType);
        self::assertSame(AddCorsHeaderForAppDomainsMiddleware::DEFAULT_ALLOWED_ORIGINS, $env->corsAllowedOrigins);
    }

    public function testCorsOriginsAreTrimmedAndEmptiesDropped(): void
    {
        $env = EnvSettings::fromArray(['CORS_ALLOWED_ORIGINS' => ' https://a.example.org , ,https://b.example.org,'] + $this->validEnv(), '.env');
        self::assertSame(['https://a.example.org', 'https://b.example.org'], $env->corsAllowedOrigins);

        $env = EnvSettings::fromArray(['CORS_ALLOWED_ORIGINS' => ''] + $this->validEnv(), '.env');
        self::assertSame(AddCorsHeaderForAppDomainsMiddleware::DEFAULT_ALLOWED_ORIGINS, $env->corsAllowedOrigins);
    }

    public function testMissingRequiredKeyThrows(): void
    {
        $values = $this->validEnv();
        unset($values['MAIL_DSN']);

        $this->expectException(LogicException::class);
        EnvSettings::fromArray($values, '.env');
    }

    public function testNonStringValueThrows(): void
    {
        $this->expectException(LogicException::class);
        EnvSettings::fromArray(['REDIS_HOST' => 42] + $this->validEnv(), '.env');
    }

    public function testUnknownLoggerLevelThrows(): void
    {
        $this->expectException(LogicException::class);
        EnvSettings::fromArray(['LOGGER_LEVEL' => 'LOUD'] + $this->validEnv(), '.env');
    }

    public function testSqliteIsAllowedOnlyForTestEnvFile(): void
    {
        $env = EnvSettings::fromArray(['DB_TYPE' => 'sqlite'] + $this->validEnv(), 'env.testing');
        self::assertSame('sqlite', $env->dbType);

        $this->expectException(ValidationException::class);
        EnvSettings::fromArray(['DB_TYPE' => 'sqlite'] + $this->validEnv(), '.env');
    }
}
