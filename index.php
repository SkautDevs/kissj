<?php

use DI\ContainerBuilder;

if (PHP_SAPI === 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__.$url['path'];
    if (is_file($file)) {
        return false;
    }
}

$version = explode('.', PHP_VERSION);
if ($version[0] < 7) {
    echo 'You are using PHP 5 or less - please update into PHP 7';
    die();
}

require __DIR__.'/vendor/autoload.php';

session_start();

// Instantiate the app

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions((new \kissj\Settings\Settings())->getSettingsAndDependencies());
$containerBuilder->useAnnotations(true); // in AbstrackController
$container = $containerBuilder->build();
$app = new \Slim\App($container);

// Register middleware
require __DIR__.'/src/middleware.php';

// Register routes
require __DIR__.'/src/routes.php';

// Run app
$app->run();
