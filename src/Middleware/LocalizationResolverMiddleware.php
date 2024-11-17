<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\Application\CookieHandler;
use kissj\Event\Event;
use kissj\Event\EventType\EventTypeDefault;
use Negotiation\AcceptLanguage;
use Negotiation\LanguageNegotiator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Slim\Views\Twig;
use Symfony\Component\Translation\Translator;

class LocalizationResolverMiddleware extends BaseMiddleware
{
    private const string LOCALE_COOKIE_NAME = 'locale';

    public function __construct(
        private readonly Twig $view,
        private readonly Translator $translator,
        private readonly CookieHandler $cookieHandler,
        private readonly LanguageNegotiator $negotiator,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
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
     * @return string[]
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
     * @param string[] $availableLanguages
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
