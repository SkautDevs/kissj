<?php

use Middlewares\TrailingSlash;
use Monolog\Logger;
use Selective\BasePath\BasePathMiddleware;
use Slim\Middleware\ContentLengthMiddleware;
use Slim\Views\TwigMiddleware;
use Whoops\Exception\Inspector;

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
$app->add(TwigMiddleware::createFromContainer($app, \Slim\Views\Twig::class));

// TRAILING SLASH REMOVER
$app->add(new TrailingSlash(false)); // remove trailing slash
/*
$app->add(function (Request $request, Response $response, callable $next): Response {
    $uri = $request->getUri();
    $path = $uri->getPath();
    if ($path !== '/' && substr($path, -1) === '/') {
        // permanently redirect paths with a trailing slash to their non-trailing counterpart
        $uri = $uri->withPath(substr($path, 0, -1));

        if ($request->getMethod() === 'GET') {
            return $response
                ->withHeader('Location', (string) $uri)
                ->withStatus(301);
        }

        return $next($request->withUri($uri), $response);
    }

    $response = $next($request, $response);

    return $response;
});
*/

// DEBUGGER
// keep last to execute first
if ($_ENV['DEBUG'] !== 'false') {
    $app->add(new \Zeuxisoo\Whoops\Slim\WhoopsMiddleware());
} else {
    $simplyErrorHandler = function (Throwable $exception, Inspector $inspector, $run) use ($container) {
        $title = $inspector->getExceptionName();
        $code = $exception->getCode();
        $message = $inspector->getExceptionMessage();

        $container->get(Logger::class)->error('Exception! '.$title.'('.$code.') -> '.$message);

        require 'Templates/exception.php';
        die;
    };

    // TODO add logger with mail
    $app->add(new \Zeuxisoo\Whoops\Slim\WhoopsMiddleware([], [$simplyErrorHandler]));
}
