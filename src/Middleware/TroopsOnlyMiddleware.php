<?php

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

class TroopsOnlyMiddleware extends BaseMiddleware {
    public function __construct(
        private FlashMessagesInterface $flashMessages,
        private TranslatorInterface $translator,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response {
        $user = $request->getAttribute('user');
        if ($user instanceof User
            && !in_array($user->role, [User::ROLE_TROOP_LEADER, User::ROLE_TROOP_PARTICIPANT], true)) {
            $this->flashMessages->error($this->translator->trans('flash.error.troopOnly'));

            return $this->createRedirectResponse($request, 'loginAskEmail');
        }

        return $handler->handle($request);
    }
}
