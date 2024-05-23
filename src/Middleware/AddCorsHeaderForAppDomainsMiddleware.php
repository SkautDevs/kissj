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


        if ($request->getMethod() == 'OPTIONS') { // handle preflight
            $response = (new \Slim\Psr7\Response())->withStatus(200);
        }
        else {
            $response = $handler->handle($request);
        }

        return $response->withHeader('Access-Control-Allow-Origin', 'kissj.skauting.cz')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', $request->getHeader('Access-Control-Allow-Headers') == [] ? '' : $request->getHeader('Access-Control-Allow-Headers'))
        ->withHeader('Access-Control-Allow-Credentials', 'true'); // also handle cookies
    }
}
