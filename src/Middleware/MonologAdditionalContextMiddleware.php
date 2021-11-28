<?php declare(strict_types = 1);

namespace kissj\Middleware;

use kissj\Middleware\BaseMiddleware;
use kissj\Settings\Sentry\EventContextProcessor;
use kissj\Settings\Sentry\UserContextProcessor;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;

class MonologAdditionalContextMiddleware extends BaseMiddleware {

    public function __construct(
        private Logger $logger,
    ) {}

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $user = $request->getAttribute('user');
        $this->logger->pushProcessor(
            new UserContextProcessor($user)
        );

        $event = $request->getAttribute('event');
        $this->logger->pushProcessor(
            new EventContextProcessor($event),
        );

        return $handler->handle($request);
    }

}