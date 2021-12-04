<?php

declare(strict_types=1);

namespace kissj;

use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Sentry\State\Hub;
use Sentry\State\Scope;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\Twig;
use Throwable;
use Whoops\Exception\Inspector;

class ErrorHandlerGetter
{
    private LoggerInterface $logger;
    private Twig $twig;
    private Hub $sentryHub;

    public function __construct(
        ContainerInterface $container
    ) {
        $this->logger = $container->get(Logger::class);
        $this->twig = $container->get(Twig::class);
        $this->sentryHub = $container->get(Hub::class);
    }

    public function getErrorHandler(): callable
    {
        return function (Throwable $exception, Inspector $inspector) {
            if ($exception instanceof HttpNotFoundException) {
                http_response_code(404);
                echo $this->twig->fetch('404.twig');
                die;
            }

            // Sentry client is wrapped in Sentry\State\Hub::withScope(...) to pass HTTP, Event and User context
            $this->sentryHub->withScope(function(Scope $scope) use ($exception, $inspector): void {
                $scope->setFingerprint([md5($inspector->getExceptionName())]);

                $this->sentryHub->captureException($exception);
            });

            $title = $inspector->getExceptionName();
            $code = $exception->getCode();
            $message = $inspector->getExceptionMessage();

            $this->logger->error('Exception! ' . $title . '(' . $code . ') -> ' . $message, [ 'exception' => $exception ]);

            http_response_code(500);
            require __DIR__ . '/Templates/exception.php';
            die;

            // TODO add logger with mail
        };
    }
}
