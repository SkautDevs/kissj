<?php

namespace kissj\Application;

use DI\Bridge\Slim\Bridge;
use kissj\Settings\Settings;
use Slim\App;

class ApplicationGetter
{
    public function getApp(
        string $envPath = __DIR__.'/../../',
        string $envFilename = '.env',
        string $tempPath = __DIR__.'/../../temp'
    ): App {
        $containerBuilder = new \DI\ContainerBuilder();
        $containerBuilder->addDefinitions((new Settings())->getContainerDefinition(
            $envPath,
            $envFilename,
        ));
        $containerBuilder->useAttributes(true); // used in AbstractController
        if ($_ENV['DEBUG'] === 'false') {
            $containerBuilder->enableCompilation($tempPath);
        }

        $container = $containerBuilder->build();
        $app = Bridge::create($container);
        $app->setBasePath($_ENV['BASEPATH']);

        $app = (new Middleware())->addMiddlewaresInto($app);
        $app = (new Route())->addRoutesInto($app);

        return $app;
    }
}
