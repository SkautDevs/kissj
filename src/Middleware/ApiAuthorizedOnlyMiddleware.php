<?php

namespace kissj\Middleware;

use kissj\Event\EventRepository;
use kissj\FlashMessages\FlashMessagesInterface;
use kissj\User\UserController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApiAuthorizedOnlyMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly EventRepository        $eventRepository
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $secret = $request->getHeader('Authorization')[0];

        if (!$secret) {
            return (new \Slim\Psr7\Response())->withStatus(401);
        }
        $eventsWithAccess = $this->eventRepository->findByApiSecret(explode(" ", $secret)[1]);
        if (!$eventsWithAccess) {
            return (new \Slim\Psr7\Response())->withStatus(401);
        }
        $request = $request->withAttribute('eventsWithAccess', $eventsWithAccess);

        return $handler->handle($request);
    }

}
