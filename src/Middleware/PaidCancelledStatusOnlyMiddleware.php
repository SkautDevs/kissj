<?php

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Psr\Log\LoggerInterface;

class PaidCancelledStatusOnlyMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly FlashMessagesInterface $flashMessages,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $user = $this->tryGetUser($request);
        if ($user === null) {
            $this->flashMessages->warning('flash.warning.notLogged');

            return $this->createRedirectResponse($request, 'loginAskEmail');
        }

        if ($user->status->isPaidOrCancelled() === false) {
            $this->logger->warning(
                'User is trying to access "paid or cancelled only" route without paid or cancelled status',
                ['email' => $user->email, 'status' => $user->status->value],
            );
            $this->flashMessages->error('flash.error.paidOrCancelledStatusRequired');

            return $this->createRedirectResponse($request, 'dashboard');
        }

        return $handler->handle($request);
    }
}
