<?php

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;

class OwnerTicketTransferAllowedOnlyMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly FlashMessagesInterface $flashMessages,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        if ($this->tryGetEvent($request)?->getEventType()->isOwnerTicketTransferAllowed() === false) {
            $this->flashMessages->error('flash.error.ownerTransferNotAllowed');

            return $this->createRedirectResponse($request, 'dashboard');
        }

        return $handler->handle($request);
    }
}
