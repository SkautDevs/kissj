<?php

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\User\UserController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;

class LoggedOnlyMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly FlashMessagesInterface $flashMessages,
        private readonly UserController $userController,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $user = $this->tryGetUser($request);
        if ($user === null) {
            $this->flashMessages->warning('flash.warning.notLogged');

            return $this->createRedirectResponse($request, 'loginAskEmail');
        }

        $event = $this->tryGetEvent($request);
        if ($event === null) {
            throw new \Exception('Cannot get event from request');
        }

        if ($user->event->id !== $event->id) {
            $this->flashMessages->warning('flash.warning.wrongEvent');

            return $this->userController->logout($request, new \Slim\Psr7\Response());
        }

        return $handler->handle($request);
    }
}
