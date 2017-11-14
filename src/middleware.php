<?php

use Slim\Http\Request;
use Slim\Http\Response;

// DEBUGGER

$app->add(new \Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware($app));


// TRAILING SLASH REMOVER

$app->add(function (Request $request, Response $response, callable $next) {
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

$app->add(function (Request $request, Response $response, callable $next) use ($container) {
	$userSevice = $container->userService;
	$canRecreateUserFromSession = $userSevice->canRecreateUserFromSession($_SESSION['user'] ?? null);
	if ($canRecreateUserFromSession) {
		$request = $request->withAttribute('user', $userSevice->createUserFromSession($_SESSION['user']));
	}
	
	$response = $next($request, $response);
	return $response;
});