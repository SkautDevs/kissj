<?php
declare(strict_types=1);

namespace kissj\Application;

use kissj\Middleware\EventInfoMiddleware;
use kissj\Middleware\LocalizationResolverMiddleware;
use kissj\Middleware\MonologAdditionalContextMiddleware;
use kissj\Middleware\UserAuthenticationMiddleware;
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
use Zeuxisoo\Whoops\Slim\WhoopsMiddleware;

class Middleware {
    public function addMiddlewaresInto(App $app): App {
        // CONTENT LENGTH
        $app->add(new ContentLengthMiddleware());

        // LOCALIZATION RESOLVER
        // https://github.com/willdurand/Negotiation
        $app->add(LocalizationResolverMiddleware::class);

        // TODO CSRF PROTECTION
        // https://github.com/slimphp/Slim-Csrf

        // Monolog *ContextProcessor hydration
        $app->add(MonologAdditionalContextMiddleware::class);

        // USER AUTHENTICATION
        $app->add(UserAuthenticationMiddleware::class);
        
        // EVENT INFO
        $app->add(EventInfoMiddleware::class);

        // ROUTING
        $app->addRoutingMiddleware();
        $app->add(new BasePathMiddleware($app)); // must be after addRoutingMiddleware()

        // TWIG
        $app->add(TwigMiddleware::createFromContainer($app, Twig::class));

        // TRAILING SLASH REMOVER
        $app->add(new TrailingSlash(false)); // remove trailing slash

        // DEBUGGER
        // keep last to execute first
        if ($_ENV['DEBUG'] === 'true') {
            $app->add(new WhoopsMiddleware());
        } else {
            // TODO move into middleware
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
            $app->add(new WhoopsMiddleware([], [$simplyErrorHandler]));
        }

        return $app;
    }
}
