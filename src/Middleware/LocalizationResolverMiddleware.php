<?php

declare(strict_types=1);

namespace kissj\Middleware;

use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
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
    private const LOCALE_COOKIE_NAME = 'locale';

    public function __construct(
        private readonly Twig $view,
        private readonly Translator $translator,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $bestLanguage = $this->getBestLanguage($request);

        $this->translator->setLocale($bestLanguage);
        $this->view->getEnvironment()->addGlobal('locale', $bestLanguage); // used in templates

        $response = $handler->handle($request);

        if (isset($request->getQueryParams()[self::LOCALE_COOKIE_NAME])) {
            $response = FigResponseCookies::remove($response, self::LOCALE_COOKIE_NAME);
            $response = FigResponseCookies::set(
                $response,
                SetCookie::create(self::LOCALE_COOKIE_NAME, $bestLanguage)
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

        $cookieLanguage = FigRequestCookies::get($request, self::LOCALE_COOKIE_NAME)->getValue();
        if ($cookieLanguage !== null && in_array($cookieLanguage, $availableLanguages, true)) {
            return $cookieLanguage;
        }

        return $this->negotiateBestLanguage($request, $availableLanguages);
    }

    /**
     * @param Request $request
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
     * @param Request $request
     * @param string[] $availableLanguages
     * @return string
     */
    private function negotiateBestLanguage(Request $request, array $availableLanguages): string
    {
        $defaultLocale = reset($availableLanguages);
        if ($defaultLocale === false) {
            throw new \RuntimeException('available languages cannot be empty');
        }

        $negotiator = new LanguageNegotiator();
        $header = $request->getHeaderLine('Accept-Language');
        if ($header === '') {
            return $defaultLocale;
        }
        /** @var ?AcceptLanguage $negotiatedLanguage */
        $negotiatedLanguage = $negotiator->getBest($header, $availableLanguages);

        if ($negotiatedLanguage === null || !in_array($negotiatedLanguage->getValue(), $availableLanguages, true)) {
            return $defaultLocale;
        }

        return $negotiatedLanguage->getValue();
    }
}
