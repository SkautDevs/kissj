<?php

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\User\UserStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;

class PaidStatusOnlyMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly FlashMessagesInterface $flashMessages,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $user = $this->tryGetUser($request);
        if ($user !== null && $user->status !== UserStatus::Paid) {
            $this->flashMessages->error('flash.error.paidStatusRequired');

            return $this->createRedirectResponse($request, 'dashboard');
        }

        return $handler->handle($request);
    }
}
