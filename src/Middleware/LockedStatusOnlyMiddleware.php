<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Psr\Log\LoggerInterface;
use RuntimeException;

class LockedStatusOnlyMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $user = $request->getAttribute('user');

        if (!$user instanceof User || !$user->status->isLocked()) {
            $this->logger->warning(
                'User ' . ($user instanceof User ? $user->email : 'unknown')
                . ' tried to access after-lock edit with status "' . ($user instanceof User ? $user->status->value : 'none') . '"'
            );

            throw new RuntimeException('You can only edit details after lock when your registration is locked');
        }

        return $handler->handle($request);
    }
}
