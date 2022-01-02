<?php declare(strict_types=1);

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__, 'env.testing');
$dotenv->load();

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
            'name' => __DIR__.'/temp/db_tests',
            'suffix' => '.sqlite',
            'charset' => 'utf8',
        ],
		'postgresql' =>[
			'adapter' => 'pgsql',
			'host' => $_ENV['DATABASE_HOST'],
			'name' => $_ENV['POSTGRES_DB'],
			'user' => $_ENV['POSTGRES_USER'],
			'pass' => $_ENV['POSTGRES_PASSWORD'],
		],
	],
];

