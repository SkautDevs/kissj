<?php

namespace kissj\Application;

use DI\Bridge\Slim\Bridge;
use DI\ContainerBuilder;
use kissj\Settings\Settings;
use Sentry\Tracing\TransactionContext;
use Slim\App;
use function Sentry\startTransaction;

class ApplicationGetter
{
    public function getApp(
        string $envPath = __DIR__.'/../../',
        string $envFilename = '.env',
        string $tempPath = __DIR__.'/../../temp'
    ): App {
        $settings = new Settings($envPath, $envFilename);

        $sentryHub = $settings->initSentry();
        $sentryTransaction = startTransaction(new TransactionContext('settings'));
        $sentryHub->setSpan($sentryTransaction);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(
            $settings->getContainerDefinition($sentryHub),
        );
        $containerBuilder->useAttributes(true); // used in AbstractController

        if ($_ENV['DEBUG'] === 'false') {
            // TODO add autowired definitions into container to get more performace
            // https://php-di.org/doc/performances.html#optimizing-for-compilation
            $containerBuilder->enableCompilation($tempPath);
        }
        $container = $containerBuilder->build();
        $app = Bridge::create($container);
        $app->setBasePath($_ENV['BASEPATH']);

        $app = (new Middleware())->addMiddlewaresInto($app);
        $app = (new Route())->addRoutesInto($app);
        
        $sentryTransaction->finish();

        return $app;
    }
}
