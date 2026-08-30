<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesBySession;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Csrf\Guard;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Routing\RouteContext;

abstract class CsrfMiddleware extends Guard
{
    public function __construct(FlashMessagesBySession $flashMessages)
    {
        parent::__construct(new ResponseFactory(), persistentTokenMode: true);

        $this->setFailureHandler(
            function (ServerRequestInterface $request) use ($flashMessages): ResponseInterface {
                $flashMessages->error('flash.error.formExpired');
                $routeContext = RouteContext::fromRequest($request);
                $eventSlug = (string) $routeContext->getRoute()?->getArgument('eventSlug');

                return (new ResponseFactory())->createResponse(302)->withHeader(
                    'Location',
                    $routeContext->getRouteParser()->urlFor(
                        $this->getFailureRedirectRouteName(),
                        ['eventSlug' => $eventSlug],
                    ),
                );
            },
        );
    }

    abstract protected function getFailureRedirectRouteName(): string;
}
