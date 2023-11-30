<?php

declare(strict_types=1);

namespace kissj;

use kissj\Logging\Sentry\SentryCollector;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\Twig;
use Throwable;
use Whoops\Exception\Inspector;

class ErrorHandlerGetter
{
    private readonly LoggerInterface $logger;
    private readonly Twig $twig;
    private readonly SentryCollector $sentryCollector;

    public function __construct(
        ContainerInterface $container
    ) {
        /** @var LoggerInterface $logger */
        $logger = $container->get(Logger::class);
        $this->logger = $logger;

        /** @var Twig $twig */
        $twig = $container->get(Twig::class);
        $this->twig = $twig;

        /** @var SentryCollector $sentryCollector */
        $sentryCollector = $container->get(SentryCollector::class);
        $this->sentryCollector = $sentryCollector;
    }

    public function getErrorHandler(): callable
    {
        return function (Throwable $throwable, Inspector $inspector) {
            if ($throwable instanceof HttpNotFoundException) {
                http_response_code(404);
                echo $this->twig->fetch('404.twig');
                die;
            }

            $this->sentryCollector->collect($throwable);

            $title = $inspector->getExceptionName();
            $code = $throwable->getCode();
            $message = $inspector->getExceptionMessage();

            $this->logger->error('Throwable! ' . $title . '(' . $code . ') -> ' . $message, [ 'exception' => $throwable ]);

            http_response_code(500);
            require __DIR__ . '/Templates/exception.php';
            die;

            // TODO add logger with mail
        };
    }
}
