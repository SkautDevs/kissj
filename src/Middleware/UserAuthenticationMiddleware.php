<?php

namespace kissj\Middleware;

use kissj\User\UserRegeneration;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;

class UserAuthenticationMiddleware extends BaseMiddleware {
    private UserRegeneration $userRegeneration;

    public function __construct(UserRegeneration $userRegeneration) {
        $this->userRegeneration = $userRegeneration;
    }

    public function process(Request $request, ResponseHandler $handler): Response {
        $user = $this->userRegeneration->getCurrentUser();
        $request = $request->withAttribute('user', $user);

        return $handler->handle($request);
    }
}
