<?php

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\User\UserStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Psr\Log\LoggerInterface;

class OpenStatusOnlyMiddleware extends BaseMiddleware
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

        if ($user->status !== UserStatus::Open) {
            $this->logger->warning(
                'User is trying to change data without open status',
                ['email' => $user->email, 'status' => $user->status->value],
            );
            $this->flashMessages->error('flash.error.openStatusRequired');

            return $this->createRedirectResponse($request, 'dashboard');
        }

        return $handler->handle($request);
    }
}
