<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Psr\Log\LoggerInterface;

class LockedStatusOnlyMiddleware extends BaseMiddleware
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

        if ($user->status->isLocked() === false) {
            $this->logger->warning(
                'User tried to access after-lock edit without locked status',
                ['email' => $user->email, 'status' => $user->status->value],
            );
            $this->flashMessages->error('flash.error.lockedStatusRequired');

            return $this->createRedirectResponse($request, 'dashboard');
        }

        return $handler->handle($request);
    }
}
