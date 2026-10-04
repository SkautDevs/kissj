<?php

declare(strict_types=1);

namespace kissj\Settings;

use Dotenv\Exception\ValidationException;
use kissj\Middleware\AddCorsHeaderForAppDomainsMiddleware;
use LogicException;
use Monolog\Level;

readonly class EnvSettings
{
    /**
     * @param list<string> $corsAllowedOrigins
     */
    public function __construct(
        public string $appName,
        public string $basePath,
        public bool $debug,
        public bool $templateCache,
        public bool $skautisUseTest,
        public string $defaultLocale,
        public Level $loggerLevel,
        public string $mailDsn,
        public string $fileHandlerType,
        public string $s3Bucket,
        public string $s3Key,
        public string $s3Secret,
        public string $s3Region,
        public string $s3Endpoint,
        public string $dbType,
        public string $databasePath,
        public string $databaseHost,
        public string $postgresUser,
        public string $postgresPassword,
        public string $postgresDb,
        public string $sentryDsn,
        public float $sentryTracesSampleRate,
        public float $sentryProfilesSampleRate,
        public string $gitHash,
        public string $redisHost,
        public int $redisPort,
        public string $redisPassword,
        public array $corsAllowedOrigins,
    ) {
    }

    /**
     * @param array<mixed> $env
     */
    public static function fromArray(array $env, string $envFilename): self
    {
        $dbType = self::string($env, 'DB_TYPE', 'postgresql');
        if ($dbType === 'sqlite' && $envFilename !== 'env.testing') {
            throw new ValidationException('DB_TYPE=sqlite is supported only for the test suite (env.testing).');
        }

        $corsAllowedOrigins = array_values(array_filter(
            array_map(trim(...), explode(',', self::string($env, 'CORS_ALLOWED_ORIGINS', ''))),
            static fn (string $origin): bool => $origin !== '',
        ));

        return new self(
            appName: self::string($env, 'APP_NAME'),
            basePath: self::string($env, 'BASEPATH'),
            debug: self::string($env, 'DEBUG') === 'true',
            templateCache: self::string($env, 'TEMPLATE_CACHE') !== 'false',
            skautisUseTest: self::string($env, 'SKAUTIS_USE_TEST') !== 'false',
            defaultLocale: self::string($env, 'DEFAULT_LOCALE'),
            loggerLevel: self::level(self::string($env, 'LOGGER_LEVEL')),
            mailDsn: self::string($env, 'MAIL_DSN'),
            fileHandlerType: self::string($env, 'FILE_HANDLER_TYPE'),
            s3Bucket: self::string($env, 'S3_BUCKET'),
            s3Key: self::string($env, 'S3_KEY'),
            s3Secret: self::string($env, 'S3_SECRET'),
            s3Region: self::string($env, 'S3_REGION'),
            s3Endpoint: self::string($env, 'S3_ENDPOINT'),
            dbType: $dbType,
            databasePath: self::string($env, 'DATABASE_PATH'),
            databaseHost: self::string($env, 'DATABASE_HOST'),
            postgresUser: self::string($env, 'POSTGRES_USER'),
            postgresPassword: self::string($env, 'POSTGRES_PASSWORD'),
            postgresDb: self::string($env, 'POSTGRES_DB'),
            sentryDsn: self::string($env, 'SENTRY_DSN'),
            sentryTracesSampleRate: (float)self::string($env, 'SENTRY_TRACES_SAMPLE_RATE', '1'),
            sentryProfilesSampleRate: (float)self::string($env, 'SENTRY_PROFILES_SAMPLE_RATE', '1'),
            gitHash: self::string($env, 'GIT_HASH'),
            redisHost: self::string($env, 'REDIS_HOST'),
            redisPort: (int)self::string($env, 'REDIS_PORT'),
            redisPassword: self::string($env, 'REDIS_PASSWORD'),
            corsAllowedOrigins: $corsAllowedOrigins === []
                ? AddCorsHeaderForAppDomainsMiddleware::DEFAULT_ALLOWED_ORIGINS
                : $corsAllowedOrigins,
        );
    }

    /**
     * @param array<mixed> $env
     */
    private static function string(array $env, string $name, ?string $default = null): string
    {
        $value = $env[$name] ?? $default;
        if (is_string($value) === false) {
            throw new LogicException('Environment variable ' . $name . ' is missing or not a string');
        }

        return $value;
    }

    private static function level(string $name): Level
    {
        foreach (Level::cases() as $level) {
            if ($level->getName() === $name) {
                return $level;
            }
        }

        throw new LogicException('Unknown logger level ' . $name);
    }
}
