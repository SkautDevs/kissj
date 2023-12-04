<?php

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable('/../../');
$dotenv->safeload();

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
		],
	],
];
