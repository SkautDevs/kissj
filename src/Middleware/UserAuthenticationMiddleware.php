<?php

namespace kissj\Middleware;

use kissj\User\UserRegeneration;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;

class UserAuthenticationMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly UserRegeneration $userRegeneration,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $user = $this->userRegeneration->getCurrentUser();
        $request = $request->withAttribute('user', $user);

        return $handler->handle($request);
    }
}
