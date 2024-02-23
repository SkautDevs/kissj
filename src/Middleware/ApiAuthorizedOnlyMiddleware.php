<?php

namespace kissj\Middleware;

use kissj\Event\EventRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Slim\Psr7\Response as ResponsePsr7;

class ApiAuthorizedOnlyMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly EventRepository $eventRepository
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $authorizationHeader = $request->getHeader('Authorization');
        if ($authorizationHeader === []) {
            return $this->getUnauthorizedResponse('missing Authorization header');
        }

        $secret = $authorizationHeader[0];
        if (str_starts_with($secret, 'Bearer ') === false) {
            return $this->getUnauthorizedResponse('missing "Bearer " in Authorization header');
        }

        $authorizedEvent = $this->eventRepository->findByApiSecret(substr($secret, 7));
        if ($authorizedEvent === null) {
            return $this->getUnauthorizedResponse('no event exists with this authorization');
        }

        return $handler->handle(
            $request->withAttribute('authorizedEvent', $authorizedEvent),
        );
    }

    private function getUnauthorizedResponse(string $reason): ResponsePsr7
    {
        $response = (new ResponsePsr7())->withStatus(401);
        $response->getBody()->write('Unauthorized - ' . $reason);

        return $response;
    }
}
