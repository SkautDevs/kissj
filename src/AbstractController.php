<?php

namespace kissj;

use DI\Annotation\Inject;
use kissj\FlashMessages;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Interfaces\RouteParserInterface;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractController {
    /**
     * @Inject()
     * @var FlashMessages\FlashMessagesBySession
     */
    protected $flashMessages;

    /**
     * @Inject("Psr\Log\LoggerInterface")
     * @var Logger
     */
    protected $logger;

    /**
     * @Inject("Slim\Views\Twig")
     * @var Twig
     */
    protected $view;

    /**
     * @Inject()
     * @var TranslatorInterface
     */
    protected $translator;

    protected function redirect(
        Request $request,
        Response $response,
        string $routeName,
        array $arguments = []
    ): Response {
        return $response
            ->withHeader('Location', $this->getRouter($request)->urlFor($routeName, $arguments))
            ->withStatus(302);
    }

    protected function getRouter(Request $request): RouteParserInterface {
        return RouteContext::fromRequest($request)->getRouteParser();
    }
}
