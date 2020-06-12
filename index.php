<?php

require __DIR__.'/vendor/autoload.php';

session_start();

$containerBuilder = new \DI\ContainerBuilder();
if ($_ENV['debug'] === 'true') {
    // TODO  add autowired definitions into container to get more performace
    // https://php-di.org/doc/performances.html#optimizing-for-compilation
    $containerBuilder->enableCompilation(__DIR__.'/temp');
}
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
