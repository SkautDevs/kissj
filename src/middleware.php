<?php
// Application middleware


// DEBUGGER

$app->add(new \Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware($app));


// EMPTY MIDDLEWARE

$app->add(function (\Slim\Http\Request $request, \Slim\Http\Response $response, $next) {
	$response = $next($request, $response);
	return $response;
});