<?php
declare(strict_types=1);

namespace kissj\Application;

use Middlewares\TrailingSlash;
use Monolog\Logger;
use Selective\BasePath\BasePathMiddleware;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Slim\Middleware\ContentLengthMiddleware;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Throwable;
use Whoops\Exception\Inspector;

class Middleware {
    public function addMiddlewaresInto(App $app): App {
        // CONTENT LENGTH
        $app->add(new ContentLengthMiddleware());

        // LOCALIZATION RESOLVER
        // https://github.com/willdurand/Negotiation
        $app->add(\kissj\Middleware\LocalizationResolverMiddleware::class);

        // TODO CSRF PROTECTION
        // https://github.com/slimphp/Slim-Csrf

        // USER AUTHENTICATION
        $app->add(\kissj\Middleware\UserAuthenticationMiddleware::class);

        // ROUTING
        $app->addRoutingMiddleware();
        $app->add(new BasePathMiddleware($app)); // must be after addRoutingMiddleware()

        // TWIG
        $app->add(TwigMiddleware::createFromContainer($app, Twig::class));

        // TRAILING SLASH REMOVER
        $app->add(new TrailingSlash(false)); // remove trailing slash

        // DEBUGGER
        // keep last to execute first
        if ($_ENV['DEBUG'] !== 'false') {
            $app->add(new \Zeuxisoo\Whoops\Slim\WhoopsMiddleware());
        } else {
            $container = $app->getContainer();

            $simplyErrorHandler = function (Throwable $exception, Inspector $inspector, $run) use ($container) {
                if ($exception instanceof HttpNotFoundException) {
                    // TODO get user preferred langage from db when implemented
                    http_response_code(404);
                    echo $container->get(Twig::class)->fetch('404.twig');
                    die;
                }

                $title = $inspector->getExceptionName();
                $code = $exception->getCode();
                $message = $inspector->getExceptionMessage();

                $container->get(Logger::class)->error('Exception! '.$title.'('.$code.') -> '.$message);

                require __DIR__.'/../Templates/exception.php';
                die;
            };

            // TODO add logger with mail
            $app->add(new \Zeuxisoo\Whoops\Slim\WhoopsMiddleware([], [$simplyErrorHandler]));
        }

        return $app;
    }
}
