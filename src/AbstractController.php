<?php

declare(strict_types=1);

namespace kissj;

use DI\Attribute\Inject;
use kissj\Event\Event;
use kissj\FileHandler\FileHandler;
use kissj\FlashMessages\FlashMessagesBySession;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Interfaces\RouteParserInterface;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Log\LoggerInterface;

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
    protected FileHandler $fileHandler;

    /**
     * @param Request $request
     * @param Response $response
     * @param string $routeName
     * @param string[] $arguments
     * @return Response
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
     * @param array<string,string> $json
     */
    protected function getResponseWithJson(Response $response, array $json): Response
    {
        $encodedJson = \GuzzleHttp\json_encode($json);
        $response->getBody()->write($encodedJson);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
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
}
