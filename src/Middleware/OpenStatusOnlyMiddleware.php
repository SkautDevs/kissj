<?php

namespace kissj\Middleware;

use kissj\User\User;
use kissj\User\UserStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Psr\Log\LoggerInterface;

class OpenStatusOnlyMiddleware extends BaseMiddleware
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $user = $request->getAttribute('user');

        if ($user instanceof User && $user->getStatus() !== UserStatus::Open) {
            $this->logger->warning(
                'User ' . $user->email . ' is trying to change data, even he has role "' . $user->getStatus()->value . '"'
            );
            throw new \RuntimeException('You cannot change your data when you are not in editing status');
        }

        return $handler->handle($request);
    }
}
