<?php

declare(strict_types=1);

namespace kissj\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as SlimResponse;

class ApiEventRequiredMiddleware extends AbstractEventRequiredMiddleware
{
    protected function createMissingEventResponse(Request $request): Response
    {
        return new SlimResponse(404);
    }
}
