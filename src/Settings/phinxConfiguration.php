<?php

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__.'/../../');
$dotenv->load();

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/../Application/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/../Application/migrations/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinx_log',
        'default_environment' => 'dev',
        'dev' => [
            'adapter' => 'sqlite',
            'name' => __DIR__.'/../db_dev',
            'suffix' => '.sqlite',
            'charset' => 'utf8',
        ],
    ],
];
