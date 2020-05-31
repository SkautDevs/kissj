<?php

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class NonLoggedOnlyMiddleware extends AbstractMiddleware {
    private $flashMessages;

    public function __construct(FlashMessagesInterface $flashMessages) {
        $this->flashMessages = $flashMessages;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        return $this->process($request, $handler);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        if ($request->getAttribute('user') !== null) {
            $this->flashMessages->warning('Pardon, but your are logged - you need to sign off first');

            $url = $this->getRouter($request)->urlFor('landing');
            $response = new \Slim\Psr7\Response();

            return $response->withHeader('Location', $url)->withStatus(302);
        }

        return $handler->handle($request);
    }
}
