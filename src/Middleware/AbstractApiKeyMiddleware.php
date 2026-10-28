<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;

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
            return $this->createUnauthorizedResponse('missing Authorization header');
        }

        $secret = $authorizationHeader[0];
        if (str_starts_with($secret, 'Bearer ') === false) {
            return $this->createUnauthorizedResponse('missing "Bearer " in Authorization header');
        }

        $apiKey = substr($secret, 7);
        if ($apiKey === '') {
            return $this->createUnauthorizedResponse('empty API key');
        }

        $authorizedEvent = $this->findEventByApiKey($apiKey);
        if ($authorizedEvent === null) {
            return $this->createUnauthorizedResponse('no event exists with this authorization');
        }

        $request = $request->withAttribute('authorizedEvent', $authorizedEvent);
        foreach ($this->getAdditionalAttributes($apiKey, $authorizedEvent) as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        return $handler->handle($request);
    }
}
