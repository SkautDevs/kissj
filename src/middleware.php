<?php

use Slim\Http\Request;
use Slim\Http\Response;

// DEBUGGER

if (($container['settings']['whoopsDebug'])) {
	$app->add(new \Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware($app));
} else {
	$simplyErrorHandler = function (Exception $exception, $inspector, $run) {
		$message = $exception->getMessage();
		$title = $inspector->getExceptionName();
		$code = $exception->getCode();
		
		// TODO make nicer page
		echo "$title ($code) -> $message";
		exit;
	};
	
	$app->add(new \Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware($app, [$simplyErrorHandler]));
}

// TRAILING SLASH REMOVER

$app->add(function (Request $request, Response $response, callable $next) {
	$uri = $request->getUri();
	$path = $uri->getPath();
	if ($path != '/' && substr($path, -1) == '/') {
		// permanently redirect paths with a trailing slash to their non-trailing counterpart
		$uri = $uri->withPath(substr($path, 0, -1));
		
		if ($request->getMethod() == 'GET') {
			return $response->withRedirect((string)$uri, 301);
		} else {
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

// USER AUTHENTICATION

$app->add(function (Request $request, Response $response, callable $next) use ($container) {
	$userRegeneration = $container->get('userRegeneration');
	$roleRepository = $container->get('roleRepository');
	$user = $userRegeneration->getCurrentUser();
	if (!is_null($user)) {
		$request = $request->withAttribute('user', $user);
		$request = $request->withAttribute('role', $roleRepository->findOneBy(['userId' => $user->id]));
	}
	
	$response = $next($request, $response);
	return $response;
});