<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;

class AdminValuesAllowedOnlyMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly FlashMessagesInterface $flashMessages,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        if ($this->tryGetEvent($request)?->getEventType()->getSubcamps() === []) {
            $this->flashMessages->error('flash.error.adminValuesNotAllowed');

            return $this->createRedirectResponse($request, 'admin-dashboard');
        }

        return $handler->handle($request);
    }
}
