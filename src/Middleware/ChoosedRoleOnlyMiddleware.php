<?php

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChoosedRoleOnlyMiddleware extends BaseMiddleware {
    public function __construct(
        private FlashMessagesInterface $flashMessages,
        private TranslatorInterface $translator,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response {
        $user = $request->getAttribute('user');

        if ($user instanceof User && $user->status === User::STATUS_WITHOUT_ROLE) {
            $this->flashMessages->info($this->translator->trans('flash.info.chooseRoleNeeded'));

            $url = $this->getRouter($request)->urlFor('landing', ['eventSlug' => $user->event->slug]);
            $response = new \Slim\Psr7\Response();

            return $response->withHeader('Location', $url)->withStatus(302);
        }

        return $handler->handle($request);
    }
}
