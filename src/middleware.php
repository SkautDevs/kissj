<?php
// Application middleware

// DEBUGGER

$app->add(new \Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware($app));


// LOCALIZATION NEGOTIATOR

// https://github.com/tboronczyk/localization-middleware
// https://github.com/willdurand/Negotiation


// TRANSLATOR

// https://symfony.com/doc/current/components/translation.html


// CSRF PROTECTION

// https://github.com/slimphp/Slim-Csrf


// USER MIDDLEWARE

// TODO check and test implementation
$app->add(function (\Slim\Http\Request $request, \Slim\Http\Response $response, $next) use ($container) {
	$userSevice = $container->userService;
	if ($userSevice->canRecreateUserFromSession($_SESSION['user'])) {
		$request->user = $userSevice->createUserFromSession($_SESSION['user']);
	}
	
	$response = $next($request, $response);
	return $response;
});