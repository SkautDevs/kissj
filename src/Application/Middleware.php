<?php

declare(strict_types=1);

namespace kissj\Application;

use kissj\ErrorHandlerGetter;
use kissj\Middleware\EventInfoMiddleware;
use kissj\Middleware\LocalizationResolverMiddleware;
use kissj\Middleware\MonologContextMiddleware;
use kissj\Telemetry\Sentry\ContextMiddleware;
use kissj\Telemetry\Sentry\HttpContextMiddleware;
use kissj\Telemetry\Sentry\TransactionMiddleware;
use kissj\Middleware\UserAuthenticationMiddleware;
use Middlewares\TrailingSlash;
use Psr\Container\ContainerInterface;
use Selective\BasePath\BasePathMiddleware;
use Slim\App;
use Slim\Middleware\ContentLengthMiddleware;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Zeuxisoo\Whoops\Slim\WhoopsMiddleware;

class Middleware
{
    /**
     * @param App<ContainerInterface> $app
     * @return App<ContainerInterface>
     */
    public function addMiddlewaresInto(App $app): App
    {
        // CONTENT LENGTH
        $app->add(new ContentLengthMiddleware());

        // LOCALIZATION RESOLVER
        // https://github.com/willdurand/Negotiation
        $app->add(LocalizationResolverMiddleware::class);

        // TODO CSRF PROTECTION
        // https://github.com/slimphp/Slim-Csrf

        // Monolog additional context
        $app->add(MonologContextMiddleware::class);

        // additional context for telemetry
        $app->add(ContextMiddleware::class);

        // USER AUTHENTICATION
        $app->add(UserAuthenticationMiddleware::class);

        // EVENT INFO
        $app->add(EventInfoMiddleware::class);

        // HTTP request context for telemetry
        $app->add(HttpContextMiddleware::class);

        // ROUTING
        $app->addRoutingMiddleware();
        $app->add(new BasePathMiddleware($app)); // must be after addRoutingMiddleware()

        // TWIG
        $app->add(TwigMiddleware::createFromContainer($app, Twig::class));

        // TRAILING SLASH REMOVER
        $app->add(new TrailingSlash(false)); // remove trailing slash

        // DEBUGGER - keep as last as possible to execute as soon as possible
        $errorHandlers = [];
        if ($_ENV['DEBUG'] !== 'true') {
            $container = $app->getContainer();
            if ($container === null) {
                throw new \RuntimeException('Cannot get container');
            }
            $errorHandlers = [(new ErrorHandlerGetter($container))->getErrorHandler()];
        }
        $app->add(new WhoopsMiddleware([], $errorHandlers));

        // TELEMETRY - must be the absolute outermost middleware so that
        $app->add(TransactionMiddleware::class);

        return $app;
    }
}
