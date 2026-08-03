<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Slim\Psr7\Response as ResponsePsr7;

abstract class AbstractApiKeyMiddleware extends BaseMiddleware
{
    public function __construct(
        protected readonly EventRepository $eventRepository,
    ) {
    }

    abstract protected function findEventByApiKey(string $apiKey): ?Event;

    /**
     * @return array<string, mixed>
     */
    protected function getAdditionalAttributes(string $apiKey, Event $authorizedEvent): array
    {
        return [];
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

        $apiKey = substr($secret, 7);
        if ($apiKey === '') {
            return $this->getUnauthorizedResponse('empty API key');
        }

        $authorizedEvent = $this->findEventByApiKey($apiKey);
        if ($authorizedEvent === null) {
            return $this->getUnauthorizedResponse('no event exists with this authorization');
        }

        $request = $request->withAttribute('authorizedEvent', $authorizedEvent);
        foreach ($this->getAdditionalAttributes($apiKey, $authorizedEvent) as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        return $handler->handle($request);
    }

    private function getUnauthorizedResponse(string $reason): ResponsePsr7
    {
        $response = (new ResponsePsr7())->withStatus(401);
        $response->getBody()->write('Unauthorized - ' . $reason);

        return $response;
    }
}
