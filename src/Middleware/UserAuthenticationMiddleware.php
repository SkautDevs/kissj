<?php

namespace kissj\Middleware;

use kissj\User\UserRegeneration;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserAuthenticationMiddleware extends BaseMiddleware {
    private $userRegeneration;

    public function __construct(UserRegeneration $userRegeneration) {
        $this->userRegeneration = $userRegeneration;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $user = $this->userRegeneration->getCurrentUser();
        $request = $request->withAttribute('user', $user);

        return $handler->handle($request);
    }
}
