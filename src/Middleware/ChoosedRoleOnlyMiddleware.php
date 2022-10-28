<?php

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\User\User;
use kissj\User\UserStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChoosedRoleOnlyMiddleware extends BaseMiddleware
{
    public function __construct(
        private FlashMessagesInterface $flashMessages,
        private TranslatorInterface $translator,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $user = $request->getAttribute('user');

        if ($user instanceof User && $user->status === UserStatus::WithoutRole) {
            $this->flashMessages->info($this->translator->trans('flash.info.chooseRoleNeeded'));

            return $this->createRedirectResponse($request, 'landing');
        }

        return $handler->handle($request);
    }
}
