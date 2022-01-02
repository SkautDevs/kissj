<?php declare(strict_types=1);

namespace kissj\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Sentry\State\Hub;
use Sentry\State\Scope;

final class SentryHttpContextMiddleware extends BaseMiddleware {

    public function __construct(
        private Hub $hub,
    ) {}

    public function process(Request $request, ResponseHandler $handler): Response
    {
        // Using $_SERVER instead of $request, because $request is Psr\Http\Message\ServerRequestInterface and not Slim\Psr7\Request (type hint)
        // Even though, the Slim implementation will be passed, the Interface does not provide methods like ::getUri() or ::getHeader($header)
        $this->hub->configureScope(function (Scope $scope): void {
            $scope->setContext('http', [
                'url' => $_SERVER['REQUEST_URI'] ?? '',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'http_method' => $_SERVER['REQUEST_METHOD'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            ]);
        });

        return $handler->handle($request);
    }

}