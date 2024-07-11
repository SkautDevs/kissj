<?php

namespace kissj\Application;

use DI\Bridge\Slim\Bridge;
use DI\Container;
use DI\ContainerBuilder;
use kissj\Session\RedisSessionHandler;
use kissj\Settings\Settings;
use SessionHandlerInterface;
use Slim\App;

class ApplicationGetter
{
    /**
     * @return App<Container>
     */
    public function getApp(
        string $envPath = __DIR__.'/../../',
        string $envFilename = '.env',
        string $tempPath = __DIR__.'/../../temp'
    ): App {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions((new Settings())->getContainerDefinition(
            $envPath,
            $envFilename,
        ));
        $containerBuilder->useAttributes(true);
        if ($_ENV['DEBUG'] === 'false') {
            $containerBuilder->enableCompilation($tempPath);
        }

        $container = $containerBuilder->build();
        $app = Bridge::create($container);
        $app->setBasePath($_ENV['BASEPATH']);

        if (headers_sent() === false) { // because of PhpUnit handling sessions poorly
            /** @var SessionHandlerInterface|null $sessionHandler */
            $sessionHandler = $container->get(SessionHandlerInterface::class);
            if ($sessionHandler instanceof RedisSessionHandler) {
                // temporary fix for not deployed Redis in infrastructure, remove if when deployed
                session_set_save_handler($sessionHandler, true);
            }

            session_start();
        }

        $app = (new Middleware())->addMiddlewaresInto($app);
        $app = (new Route())->addRoutesInto($app);

        return $app;
    }
}
