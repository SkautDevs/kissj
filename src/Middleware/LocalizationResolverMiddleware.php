<?php

namespace kissj\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Views\Twig;

class LocalizationResolverMiddleware implements MiddlewareInterface {
    private $view;
    private $availableLanguages;
    private $defaultLocale;

    /**
     * LocalizationResolver constructor.
     * @param Twig     $twig
     * @param string[] $availableLanguages
     * @param string   $defaultLocale
     */
    public function __construct(Twig $twig, array $availableLanguages, string $defaultLocale) {
        $this->view = $twig;
        $this->availableLanguages = $availableLanguages;
        $this->defaultLocale = $defaultLocale;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        return $this->process($request, $handler);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $negotiator = new \Negotiation\LanguageNegotiator();
        $negotiatedLanguage = $negotiator->getBest($request->getHeaderLine('Accept-Language'), $this->availableLanguages);
        $bestLanguage = $negotiatedLanguage ? $negotiatedLanguage->getValue() : $this->defaultLocale;
        $this->view->getEnvironment()->addGlobal('locale', $bestLanguage);

        return $handler->handle($request);
    }
}
