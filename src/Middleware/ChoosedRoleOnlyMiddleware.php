<?php

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ChoosedRoleOnlyMiddleware extends AbstractMiddleware {
    private $flashMessages;

    public function __construct(FlashMessagesInterface $flashMessages) {
        $this->flashMessages = $flashMessages;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        return $this->process($request, $handler);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        /** @var User $user */
        $user = $request->getAttribute('user');

        if ($user->status === User::STATUS_WITHOUT_ROLE) { // TODO fix on user === null
            $this->flashMessages->info('At first you need to choose your role on event');

            $url = $this->getRouter($request)->urlFor('landing');
            $response = new \Slim\Psr7\Response();

            return $response->withHeader('Location', $url)->withStatus(302);
        }

        return $handler->handle($request);
    }
}
