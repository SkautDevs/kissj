<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\Orm\EntityNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Slim\Psr7\Response as SlimResponse;
use Slim\Views\Twig;

class EntityNotFoundMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly Twig $twig,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        try {
            return $handler->handle($request);
        } catch (EntityNotFoundException) {
            return $this->twig->render((new SlimResponse())->withStatus(404), '404.twig');
        }
    }
}
