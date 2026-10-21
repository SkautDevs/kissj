<?php

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/../Application/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/../Application/migrations/seeds',
    ],
	'environments' => [
        'default_migration_table' => 'phinx_log',
        'default_environment' => 'postgresql',
		'postgresql' => [
			'adapter' => 'pgsql',
			'host' => $_ENV['DATABASE_HOST'],
			'name' => $_ENV['POSTGRES_DB'],
			'user' => $_ENV['POSTGRES_USER'],
			'pass' => $_ENV['POSTGRES_PASSWORD'],
			'file_handler_type' => $_ENV['FILE_HANDLER_TYPE'] ?? null,
		],
	],
];
