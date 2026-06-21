<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\Application\CookieHandler;
use kissj\Event\Event;
use kissj\Event\EventScope;
use kissj\Event\EventType\EventTypeDefault;
use kissj\Translation\CurrentTranslator;
use Negotiation\AcceptLanguage;
use Negotiation\LanguageNegotiator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Slim\Views\Twig;

class LocalizationResolverMiddleware extends BaseMiddleware
{
    private const string LOCALE_COOKIE_NAME = 'locale';

    public function __construct(
        private readonly Twig $view,
        private readonly CurrentTranslator $translator,
        private readonly EventScope $eventScope,
        private readonly CookieHandler $cookieHandler,
        private readonly LanguageNegotiator $negotiator,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        // EventInfoMiddleware sets the event-scoped translator delegate for an event if present
        if (!$this->tryGetEvent($request) instanceof Event) {
            $this->eventScope->resetToBase();
        }

        $bestLanguage = $this->getBestLanguage($request);

        $this->translator->setLocale($bestLanguage);
        $this->view->getEnvironment()->addGlobal('locale', $bestLanguage); // used in many templates

        $response = $handler->handle($request);

        if (isset($request->getQueryParams()[self::LOCALE_COOKIE_NAME])) {
            $response = $this->cookieHandler->setCookie(
                $response,
                self::LOCALE_COOKIE_NAME,
                $bestLanguage,
            );
        }

        return $response;
    }

    private function getBestLanguage(Request $request): string
    {
        $availableLanguages = $this->getAvailableLanguages($request);

        if (isset($request->getQueryParams()[self::LOCALE_COOKIE_NAME])) {
            $parameterLanguage = $request->getQueryParams()[self::LOCALE_COOKIE_NAME];
            if (in_array($parameterLanguage, $availableLanguages, true)) {
                return $parameterLanguage;
            }
        }

        $cookieLanguage = $this->cookieHandler->getCookie($request, self::LOCALE_COOKIE_NAME);
        if ($cookieLanguage !== null && in_array($cookieLanguage, $availableLanguages, true)) {
            return $cookieLanguage;
        }

        return $this->negotiateBestLanguage($request, $availableLanguages);
    }

    /**
     * @return list<string>
     */
    private function getAvailableLanguages(Request $request): array
    {
        $event = $this->tryGetEvent($request);

        if ($event instanceof Event) {
            $availableLanguages = $event->getEventType()->getLanguages();
        } else {
            $availableLanguages = (new EventTypeDefault())->getLanguages();
        }

        return array_keys($availableLanguages);
    }

    /**
     * @param list<string> $availableLanguages
     */
    private function negotiateBestLanguage(Request $request, array $availableLanguages): string
    {
        $defaultLocale = reset($availableLanguages);
        if ($defaultLocale === false) {
            throw new \RuntimeException('available languages cannot be empty');
        }

        $header = $request->getHeaderLine('Accept-Language');
        if ($header === '') {
            return $defaultLocale;
        }
        /** @var ?AcceptLanguage $negotiatedLanguage */
        $negotiatedLanguage = $this->negotiator->getBest($header, $availableLanguages);

        if ($negotiatedLanguage === null || !in_array($negotiatedLanguage->getValue(), $availableLanguages, true)) {
            return $defaultLocale;
        }

        return $negotiatedLanguage->getValue();
    }
}
