<?php

declare(strict_types=1);

namespace kissj\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EventRequiredMiddleware extends AbstractEventRequiredMiddleware
{
    protected function createMissingEventResponse(Request $request): Response
    {
        return $this->createRedirectResponse($request, 'eventList');
    }
}
