<?php

declare(strict_types=1);

namespace kissj;

use kissj\Telemetry\Sentry\Collector;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\Twig;
use Throwable;
use Whoops\Exception\Inspector;

readonly class ErrorHandlerGetter
{
    private LoggerInterface $logger;
    private Twig $twig;
    private Collector $sentryCollector;

    public function __construct(
        ContainerInterface $container
    ) {
        /** @var LoggerInterface $logger */
        $logger = $container->get(Logger::class);
        $this->logger = $logger;

        /** @var Twig $twig */
        $twig = $container->get(Twig::class);
        $this->twig = $twig;

        /** @var Collector $sentryCollector */
        $sentryCollector = $container->get(Collector::class);
        $this->sentryCollector = $sentryCollector;
    }

    public static function renderExceptionPage(string $configErrorMessage = ''): string
    {
        ob_start();
        require __DIR__ . '/Templates/exception.php';

        return (string) ob_get_clean();
    }

    public function getErrorHandler(): callable
    {
        return function (Throwable $throwable, Inspector $inspector) {
            if ($throwable instanceof HttpNotFoundException) {
                http_response_code(404);
                echo $this->twig->fetch('404.twig');
                die;
            }

            if ($throwable instanceof HttpMethodNotAllowedException) {
                http_response_code(405);
                echo $this->twig->fetch('404.twig');
                die;
            }

            $this->sentryCollector->collect($throwable);

            $title = $inspector->getExceptionName();
            $code = $throwable->getCode();
            $message = $inspector->getExceptionMessage();

            $this->logger->error('Throwable! ' . $title . '(' . $code . ') -> ' . $message, [ 'exception' => $throwable ]);

            http_response_code(500);
            echo self::renderExceptionPage();
            die;

            // TODO add logger with mail
        };
    }
}
