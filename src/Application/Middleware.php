<?php

declare(strict_types=1);

namespace kissj\Application;

use kissj\ErrorHandlerGetter;
use kissj\Middleware\EventInfoMiddleware;
use kissj\Middleware\LocalizationResolverMiddleware;
use kissj\Middleware\MonologContextMiddleware;
use kissj\Middleware\SentryContextMiddleware;
use kissj\Middleware\SentryHttpContextMiddleware;
use kissj\Middleware\UserAuthenticationMiddleware;
use Middlewares\TrailingSlash;
use Selective\BasePath\BasePathMiddleware;
use Slim\App;
use Slim\Middleware\ContentLengthMiddleware;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Zeuxisoo\Whoops\Slim\WhoopsMiddleware;

class Middleware
{
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

        // Sentry additional context
        $app->add(SentryContextMiddleware::class);

        // USER AUTHENTICATION
        $app->add(UserAuthenticationMiddleware::class);

        // EVENT INFO
        $app->add(EventInfoMiddleware::class);

        // Sentry HTTP request context
        $app->add(SentryHttpContextMiddleware::class);

        // ROUTING
        $app->addRoutingMiddleware();
        $app->add(new BasePathMiddleware($app)); // must be after addRoutingMiddleware()

        // TWIG
        $app->add(TwigMiddleware::createFromContainer($app, Twig::class));

        // TRAILING SLASH REMOVER
        $app->add(new TrailingSlash(false)); // remove trailing slash

        // DEBUGGER
        // keep last to execute first
        $errorHandlers = [];
        if ($_ENV['DEBUG'] !== 'true') {
            $container = $app->getContainer();
            if ($container === null) {
                throw new \RuntimeException('Cannot get container');
            }
            $errorHandlers = [(new ErrorHandlerGetter($container))->getErrorHandler()];
        }
        $app->add(new WhoopsMiddleware([], $errorHandlers));

        return $app;
    }
}
