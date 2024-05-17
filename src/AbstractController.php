<?php

declare(strict_types=1);

namespace kissj;

use DI\Attribute\Inject;
use kissj\Entry\EntryStatus;
use kissj\Event\Event;
use kissj\FileHandler\SaveFileHandler;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Logging\Sentry\SentryCollector;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Interfaces\RouteParserInterface;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Log\LoggerInterface;

use function GuzzleHttp\json_encode as guzzleJsonEncode;

abstract class AbstractController
{
    #[Inject]
    protected FlashMessagesBySession $flashMessages;

    #[Inject(LoggerInterface::class)]
    protected Logger $logger;

    #[Inject]
    protected Twig $view;

    #[Inject]
    protected TranslatorInterface $translator;

    #[Inject]
    protected SaveFileHandler $fileHandler;

    #[Inject]
    protected SentryCollector $sentryCollector;

    /**
     * @param string[] $arguments
     */
    protected function redirect(
        Request $request,
        Response $response,
        string $routeName,
        array $arguments = []
    ): Response {
        $event = $this->tryGetEvent($request);
        if ($event instanceof Event) {
            $arguments = array_merge($arguments, ['eventSlug' => $event->slug]);
        }

        if (array_key_exists('eventSlug', $arguments)) {
            return $response
                ->withHeader('Location', $this->getRouter($request)->urlFor($routeName, $arguments))
                ->withStatus(302);
        }

        $this->flashMessages->warning($this->translator->trans('flash.warning.nonexistentEvent'));

        return $response
            ->withHeader('Location', $this->getRouter($request)->urlFor('eventList', $arguments))
            ->withStatus(302);
    }

    /**
     * @param array<string,int|string|EntryStatus|null|array<mixed>> $json
     */
    protected function getResponseWithJson(Response $response, array $json, int $statusCode = 200): Response
    {
        $encodedJson = guzzleJsonEncode($json);
        $response->getBody()->write($encodedJson);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }

    protected function getRouter(Request $request): RouteParserInterface
    {
        return RouteContext::fromRequest($request)->getRouteParser();
    }

    protected function getEvent(Request $request): Event
    {
        /** @var Event $event */
        $event = $request->getAttribute('event');

        return $event;
    }

    protected function tryGetEvent(Request $request): ?Event
    {
        /**
         * @var Event|null $event
         */
        $event = $request->getAttribute('event');

        return $event;
    }

    protected function getParameterFromBody(Request $request, string $parameterName, bool $escapeValue = false): string
    {
        $parsedBody = $request->getParsedBody();
        if (!is_array($parsedBody)) {
            throw new \RuntimeException('getParsedBody() did not returned array');
        }

        if (!array_key_exists($parameterName, $parsedBody)) {
            throw new \RuntimeException('body does not contain parameter ' . $parameterName);
        }

        $parameter = $parsedBody[$parameterName];

        if ($escapeValue) {
            $parameter = htmlspecialchars((string)$parameter, ENT_QUOTES);
        }

        return $parameter;
    }

    protected function getParameterFromQuery(Request $request, string $parameterName): string
    {
        $queryParams = $request->getQueryParams();

        if (!array_key_exists($parameterName, $queryParams)) {
            throw new \RuntimeException('query parameter does not contain key ' . $parameterName);
        }

        return $queryParams[$parameterName];
    }

    /**
     * @return array<mixed>
     */
    protected function getParsedJsonFromBody(Request $request): array
    {
        try {
            /** @var array<mixed> $json */
            $json = json_decode((string)$request->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->sentryCollector->collect($e);

            return [];
        }

        return $json;
    }
}
