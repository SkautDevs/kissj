<?php

namespace kissj\Middleware;

use kissj\User\User;
use kissj\User\UserStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Psr\Log\LoggerInterface;

class PaidStatusOnlyMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $user = $request->getAttribute('user');

        if ($user instanceof User && $user->status !== UserStatus::Paid) {
            $this->logger->warning(
                'User ' . $user->email . ' is trying to access "paid only" route, even he has status "' . $user->status->value . '"'
            );
            throw new \RuntimeException('You cannot change your data when you are not in paid status');
        }

        return $handler->handle($request);
    }
}
