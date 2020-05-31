<?php

namespace kissj\Middleware;

use kissj\User\UserRegeneration;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserAuthenticationMiddleware implements MiddlewareInterface {
    private $userRegeneration;

    public function __construct(UserRegeneration $userRegeneration) {
        $this->userRegeneration = $userRegeneration;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        return $this->process($request, $handler);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $user = $this->userRegeneration->getCurrentUser();
        $request = $request->withAttribute('user', $user);

        return $handler->handle($request);
    }
}
