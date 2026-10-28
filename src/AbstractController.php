<?php

declare(strict_types=1);

namespace kissj;

use DI\Attribute\Inject;
use GuzzleHttp\Utils;
use kissj\Event\Event;
use kissj\FileHandler\SaveFileHandler;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Telemetry\Sentry\Collector;
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
    protected Collector $sentryCollector;

    protected function flashRegistrationCloseResult(RegistrationCloseResult $result): void
    {
        foreach ($result->warnings as $warning) {
            $params = [];
            foreach ($warning['params'] as $name => $value) {
                if (is_array($value)) {
                    $translatedLabels = array_map(
                        fn (string $labelKey): string => $this->translator->trans($labelKey),
                        $value,
                    );

                    $params[$name] = implode(', ', $translatedLabels);
                } else {
                    $params[$name] = $value;
                }
            }
            $this->flashMessages->warning($warning['key'], $params);
        }
    }

    protected function flashPaymentResult(PaymentResult $result): void
    {
        foreach ($result->messages as $message) {
            match ($message->severity) {
                PaymentMessageSeverity::Info => $this->flashMessages->info(
                    $message->translationKey,
                    $message->translationParams,
                ),
                PaymentMessageSeverity::Success => $this->flashMessages->success(
                    $message->translationKey,
                    $message->translationParams,
                ),
                PaymentMessageSeverity::Warning => $this->flashMessages->warning(
                    $message->translationKey,
                    $message->translationParams,
                ),
                PaymentMessageSeverity::Error => $this->flashMessages->error(
                    $message->translationKey,
                    $message->translationParams,
                ),
            };
        }
    }

    /**
     * @param array<string, string> $arguments
     * @param array<string, string> $queryParams
     */
    protected function redirect(
        Request $request,
        Response $response,
        string $routeName,
        array $arguments = [],
        array $queryParams = []
    ): Response {
        $event = $this->tryGetEvent($request);
        if ($event instanceof Event) {
            $arguments = array_merge($arguments, ['eventSlug' => $event->slug]);
        }

        if (array_key_exists('eventSlug', $arguments)) {
            return $response
                ->withHeader('Location', $this->getRouter($request)->urlFor($routeName, $arguments, $queryParams))
                ->withStatus(302);
        }

        $this->flashMessages->warning('flash.warning.nonexistentEvent');

        return $response
            ->withHeader('Location', $this->getRouter($request)->urlFor('eventList', $arguments))
            ->withStatus(302);
    }

    protected function getResponseWithJson(Response $response, object $json, int $statusCode = 200): Response
    {
        $encodedJson = Utils::jsonEncode($json);
        $response->getBody()->write($encodedJson);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }

    /**
     * Harden a response that streams back a user-uploaded file: uploaded bytes are attacker-controlled
     * and served from our own origin, so stop content-type sniffing and block script execution if the
     * file is opened top-level (e.g. a scripted SVG or an HTML file mislabelled as an image). `script-src
     * 'none'` is used rather than the stronger `sandbox` so legitimate inline preview of images and PDFs
     * keeps working (sandbox disables the browser's PDF viewer) while stored XSS is still neutralised.
     */
    protected function withUntrustedFileSecurityHeaders(Response $response): Response
    {
        return $response
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader('Content-Security-Policy', "script-src 'none'");
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
        $parsedBody = $this->getParsedBodyArray($request);

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

    /**
     * @return list<string>
     */
    protected function getStringListFromBody(Request $request, string $parameterName): array
    {
        $values = $this->getParsedBodyArray($request)[$parameterName] ?? [];
        if (is_array($values) === false) {
            throw new \RuntimeException('body parameter ' . $parameterName . ' is not a list');
        }

        $list = [];
        foreach ($values as $value) {
            if (is_string($value) === false) {
                throw new \RuntimeException('body parameter ' . $parameterName . ' contains a non-string value');
            }
            $list[] = $value;
        }

        return $list;
    }

    protected function isCheckboxCheckedInBody(Request $request, string $parameterName): bool
    {
        return array_key_exists($parameterName, $this->getParsedBodyArray($request));
    }

    /**
     * @return array<mixed>
     */
    private function getParsedBodyArray(Request $request): array
    {
        $parsedBody = $request->getParsedBody();
        if (is_array($parsedBody) === false) {
            throw new \RuntimeException('getParsedBody() did not return an array');
        }

        return $parsedBody;
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
