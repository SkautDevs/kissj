<?php

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PatrolLeadersOnlyMiddleware extends AbstractMiddleware {
    private $flashMessages;

    public function __construct(FlashMessagesInterface $flashMessages) {
        $this->flashMessages = $flashMessages;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        return $this->process($request, $handler);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        if ($request->getAttribute('user')->role !== User::ROLE_PATROL_LEADER) {
            $this->flashMessages->error('Pardon, nejsi na akci přihlášený jako Patrol Leader');

            $url = $this->getRouter($request)->urlFor('loginAskEmail');
            $response = new \Slim\Psr7\Response();

            return $response->withHeader('Location', $url)->withStatus(302);
        }

        return $handler->handle($request);
    }
}
