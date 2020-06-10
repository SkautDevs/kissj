<?php

require __DIR__.'/vendor/autoload.php';

session_start();

$containerBuilder = new \DI\ContainerBuilder();
$containerBuilder->addDefinitions((new \kissj\Settings\Settings())->getContainerDefinition());
$containerBuilder->useAnnotations(true); // used in AbstractController
$container = $containerBuilder->build();
$app = \DI\Bridge\Slim\Bridge::create($container);

// Register middleware // TODO move into class
require __DIR__.'/src/middleware.php';

// Register routes // TODO move into class
require __DIR__.'/src/routes.php';

// Run app
$app->run();
