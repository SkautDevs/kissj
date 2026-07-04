<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Tests\AppTestCase;

$dotenv = Dotenv::createImmutable(__DIR__, 'env.testing');
$dotenv->load();

$databasePath = is_string($_ENV['DATABASE_PATH'] ?? null)
    ? $_ENV['DATABASE_PATH']
    : __DIR__ . '/temp/run_' . (int)getmypid() . '/' . AppTestCase::DB_FILENAME;
// standalone phinx runs hit the fallback before anything created the run dir;
// @: tolerant of a concurrent suite creating it first
$databaseDir = dirname($databasePath);
if (!is_dir($databaseDir)) {
    @mkdir($databaseDir, 0777, true);
}

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/../src/Application/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/../src/Application/migrations/seeds',
    ],
	'environments' => [
        'default_migration_table' => 'phinx_log',
        'default_environment' => $_ENV['DB_TYPE'],
        'sqlite' => [
            'adapter' => 'sqlite',
            // path is set by AppTestCase::forceSqliteDbType(); the fallback above derives
            // the same per-process path for standalone phinx runs
            'name' => $databasePath,
            'suffix' => '',
            'charset' => 'utf8',
        ],
		'postgresql' => [
			'adapter' => 'pgsql',
			'host' => $_ENV['DATABASE_HOST'],
			'name' => $_ENV['POSTGRES_DB'],
			'user' => $_ENV['POSTGRES_USER'],
			'pass' => $_ENV['POSTGRES_PASSWORD'],
		],
	],
];
