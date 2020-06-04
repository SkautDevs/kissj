<?php

use Middlewares\TrailingSlash;
use Slim\Middleware\ContentLengthMiddleware;
use Slim\Views\TwigMiddleware;
use Whoops\Exception\Inspector;

$container = $app->getContainer();

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

//CORRECT SUBFOLDER -> BASE PATH
//$app->add(BasePathMiddleware::class);

// TWIG
$app->add(TwigMiddleware::createFromContainer($app));

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
if ($container->get('settings')['whoopsDebug']) {
    $app->add(new \Zeuxisoo\Whoops\Slim\WhoopsMiddleware());
} else {
    $simplyErrorHandler = function (Throwable $exception, Inspector $inspector, $run) use ($container) {
        $title = $inspector->getExceptionName();
        $code = $exception->getCode();
        $message = $inspector->getExceptionMessage();

        $container->get('logger')->error('Exception! '.$title.'('.$code.') -> '.$message);

        require 'Templates/en/exception.php';
        die;
    };

    // TODO add logger with mail
    $app->add(new \Zeuxisoo\Whoops\Slim\WhoopsMiddleware([], [$simplyErrorHandler]));
}
