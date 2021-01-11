<?php

namespace kissj\Application;

class ApplicationGetter {
    public function getApp(
        string $envPath = __DIR__.'/../../',
        string $envFilename = '.env',
        string $dbFullPath = __DIR__.'/../db_dev.sqlite', 
        string $tempPath = __DIR__.'/../../temp'
    ): \Slim\App {
        $containerBuilder = new \DI\ContainerBuilder();
        $containerBuilder->addDefinitions((new \kissj\Settings\Settings())->getContainerDefinition(
            $envPath,
            $envFilename,
            $dbFullPath
        ));
        $containerBuilder->useAnnotations(true); // used in AbstractController
        if ($_ENV['DEBUG'] === 'false') {
            // TODO add autowired definitions into container to get more performace
            // https://php-di.org/doc/performances.html#optimizing-for-compilation
            $containerBuilder->enableCompilation($tempPath);
        }
        $container = $containerBuilder->build();
        $app = \DI\Bridge\Slim\Bridge::create($container);
        $app->setBasePath($_ENV['BASEPATH']);

        $app = (new Middleware())->addMiddlewaresInto($app);
        $app = (new Route())->addRoutesInto($app);

        return $app;
    }
}
