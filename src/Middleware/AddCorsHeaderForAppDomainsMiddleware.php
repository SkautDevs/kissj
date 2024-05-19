<?php

declare(strict_types=1);

namespace kissj\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;

class AddCorsHeaderForAppDomainsMiddleware extends BaseMiddleware
{

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $response = $handler->handle($request);
        $response = $response->withAddedHeader('Access-Control-Allow-Origin', 'https://kissj.skauting.net');
        
        return $response;
    }
}
