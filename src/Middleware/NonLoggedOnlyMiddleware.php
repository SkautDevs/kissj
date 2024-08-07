<?php

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;

class NonLoggedOnlyMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly FlashMessagesInterface $flashMessages,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        if ($request->getAttribute('user') !== null) {
            $this->flashMessages->warning('flash.warning.loggedIn');

            return $this->createRedirectResponse($request, 'landing');
        }

        return $handler->handle($request);
    }
}
