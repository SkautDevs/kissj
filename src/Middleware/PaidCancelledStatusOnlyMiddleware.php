<?php

namespace kissj\Middleware;

use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Psr\Log\LoggerInterface;

class PaidCancelledStatusOnlyMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $user = $request->getAttribute('user');

        if ($user instanceof User && !$user->status->isPaidOrCancelled()) {
            $this->logger->warning(
                'User ' . $user->email . ' is trying to access "paid or cancelled only" route, even he has status "' . $user->status->value . '"'
            );
            throw new \RuntimeException('You cannot change your data when you are not in paid or cancelled status');
        }

        return $handler->handle($request);
    }
}
