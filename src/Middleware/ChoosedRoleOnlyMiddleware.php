<?php

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChoosedRoleOnlyMiddleware extends BaseMiddleware {
    private $flashMessages;
    private $translator;

    public function __construct(
        FlashMessagesInterface $flashMessages,
        TranslatorInterface $translator
    ) {
        $this->flashMessages = $flashMessages;
        $this->translator = $translator;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        /** @var User $user */
        $user = $request->getAttribute('user');

        if ($user->status === User::STATUS_WITHOUT_ROLE) { // TODO fix on user === null
            $this->flashMessages->info($this->translator->trans('flash.info.chooseRoleNeeded'));

            $url = $this->getRouter($request)->urlFor('landing');
            $response = new \Slim\Psr7\Response();

            return $response->withHeader('Location', $url)->withStatus(302);
        }

        return $handler->handle($request);
    }
}
