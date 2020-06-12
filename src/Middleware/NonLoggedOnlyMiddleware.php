<?php

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class NonLoggedOnlyMiddleware extends BaseMiddleware {
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
        if ($request->getAttribute('user') !== null) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.loggedIn'));

            $url = $this->getRouter($request)->urlFor('landing');
            $response = new \Slim\Psr7\Response();

            return $response->withHeader('Location', $url)->withStatus(302);
        }

        return $handler->handle($request);
    }
}
