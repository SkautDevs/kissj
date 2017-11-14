<?php

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// DEBUGGER

$app->add(new \Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware($app));


// TRAILING SLASH REMOVER

$app->add(function (RequestInterface $request, ResponseInterface $response, callable $next) {
	$uri = $request->getUri();
	$path = $uri->getPath();
	if ($path != '/' && substr($path, -1) == '/') {
		// permanently redirect paths with a trailing slash to their non-trailing counterpart
		$uri = $uri->withPath(substr($path, 0, -1));
		
		if($request->getMethod() == 'GET') {
			return $response->withRedirect((string)$uri, 301);
		}
		else {
			return $next($request->withUri($uri), $response);
		}
	}
	
	return $next($request, $response);
});


// LOCALIZATION NEGOTIATOR

// https://github.com/tboronczyk/localization-middleware
// https://github.com/willdurand/Negotiation


// TRANSLATOR

// https://symfony.com/doc/current/components/translation.html


// CSRF PROTECTION

// https://github.com/slimphp/Slim-Csrf


// USER AUTHENTICATION MIDDLEWARE

// TODO check and test implementation
$app->add(function (RequestInterface $request, ResponseInterface $response, callable $next) use ($container) {
	$userSevice = $container->userService;
	if ($userSevice->canRecreateUserFromSession($_SESSION['user'] ?? null)) {
		$request->user = $userSevice->createUserFromSession($_SESSION['user']);
	}
	
	$response = $next($request, $response);
	return $response;
});