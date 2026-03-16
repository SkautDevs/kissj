<?php

declare(strict_types=1);

namespace kissj;

use DI\Attribute\Inject;
use GuzzleHttp\Utils;
use kissj\Entry\EntryStatus;
use kissj\Event\Event;
use kissj\FileHandler\SaveFileHandler;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Logging\Sentry\SentryCollector;
use kissj\Participant\RegistrationCloseResult;
use kissj\Payment\PaymentMessageSeverity;
use kissj\Payment\PaymentResult;
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

    protected function flashRegistrationCloseResult(RegistrationCloseResult $result): void
    {
        foreach ($result->warnings as $warning) {
            $this->flashMessages->warning($warning['key'], $warning['params']);
        }
    }

    protected function flashPaymentResult(PaymentResult $result): void
    {
        foreach ($result->messages as $message) {
            match ($message->severity) {
                PaymentMessageSeverity::Info => $this->flashMessages->info($message->translationKey, $message->translationParams),
                PaymentMessageSeverity::Success => $this->flashMessages->success($message->translationKey, $message->translationParams),
                PaymentMessageSeverity::Warning => $this->flashMessages->warning($message->translationKey, $message->translationParams),
                PaymentMessageSeverity::Error => $this->flashMessages->error($message->translationKey, $message->translationParams),
            };
        }
    }

    /**
     * @param array<string, string> $arguments
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

        $this->flashMessages->warning('flash.warning.nonexistentEvent');

        return $response
            ->withHeader('Location', $this->getRouter($request)->urlFor('eventList', $arguments))
            ->withStatus(302);
    }

    protected function getJsonResponseFromException(Response $response, TranslatableException $e): Response
    {
        return $this->getResponseWithJson(
            $response,
            [
                'translationKey' => $e->translationKey,
                'translationMessage' => $this->translator->trans($e->translationKey),
            ],
            $e->httpStatus,
        );
    }

    /**
     * @param array<string,int|string|EntryStatus|null|array<mixed>> $json
     */
    protected function getResponseWithJson(Response $response, array $json, int $statusCode = 200): Response
    {
        $encodedJson = Utils::jsonEncode($json);
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

        if (!is_string($parsedBody[$parameterName])) {
            throw new \RuntimeException('body parameter ' . $parameterName . ' is not a string');
        }

        $parameter = $parsedBody[$parameterName];

        if ($escapeValue) {
            $parameter = htmlspecialchars($parameter, ENT_QUOTES);
        }

        return $parameter;
    }

    protected function getParameterFromQuery(Request $request, string $parameterName): string
    {
        $queryParams = $request->getQueryParams();

        if (!array_key_exists($parameterName, $queryParams)) {
            throw new \RuntimeException('query parameter does not contain key ' . $parameterName);
        }

        if (!is_string($queryParams[$parameterName])) {
            throw new \RuntimeException('query parameter ' . $parameterName . ' is not a string');
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
