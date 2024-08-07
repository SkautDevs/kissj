<?php

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;

class AdminPaymentsOnlyMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly FlashMessagesInterface $flashMessages,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $user = $request->getAttribute('user');

        if ($user instanceof User && $user->role->isEligibleToHandlePayments() === false) {
            $this->flashMessages->error('flash.error.adminPaymentsOnly');

            return $this->createRedirectResponse($request, 'getDashboard');
        }

        return $handler->handle($request);
    }
}
