<?php

namespace kissj\Middleware;

use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
use Negotiation\AcceptLanguage;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Slim\Views\Twig;
use Symfony\Component\Translation\Translator;

class LocalizationResolverMiddleware extends BaseMiddleware
{
    private const LOCALE_COOKIE_NAME = 'locale';

    /**
     * @param string[] $availableLanguages
     */
    public function __construct(
        private Twig $view,
        private Translator $translator,
        private array $availableLanguages,
        private string $defaultLocale,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        if (isset($request->getQueryParams()[self::LOCALE_COOKIE_NAME])) {
            $bestNegotiatedLanguage = htmlspecialchars($request->getQueryParams()[self::LOCALE_COOKIE_NAME],
                ENT_QUOTES);
        } else {
            $bestNegotiatedLanguage = $this->getBestLanguage($request);
        }

        $this->translator->setLocale($bestNegotiatedLanguage);
        $this->view->getEnvironment()->addGlobal('locale', $bestNegotiatedLanguage); // used in templates

        $response = $handler->handle($request);

        if (isset($request->getQueryParams()[self::LOCALE_COOKIE_NAME])) {
            $response = FigResponseCookies::remove($response, self::LOCALE_COOKIE_NAME);
            $response = FigResponseCookies::set(
                $response,
                SetCookie::create(self::LOCALE_COOKIE_NAME, $bestNegotiatedLanguage)
            );
        }

        return $response;
    }

    private function getBestLanguage(Request $request): string
    {
        $localeCookie = FigRequestCookies::get($request, self::LOCALE_COOKIE_NAME);
        if ($localeCookie->getValue() !== null) {
            return $localeCookie->getValue();
        }

        $negotiator = new \Negotiation\LanguageNegotiator();
        $header = $request->getHeaderLine('Accept-Language');
        if ($header === '') {
            return $this->defaultLocale;
        }

        /** @var AcceptLanguage $negotiatedLanguage */
        $negotiatedLanguage = $negotiator->getBest($header, $this->availableLanguages);

        return $negotiatedLanguage ? $negotiatedLanguage->getValue() : $this->defaultLocale;
    }
}
