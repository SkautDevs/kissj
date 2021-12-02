<?php

declare(strict_types=1);

namespace kissj;

use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\Twig;
use Throwable;
use Whoops\Exception\Inspector;

class ErrorHandlerGetter
{
    private LoggerInterface $logger;
    private Twig $twig;

    public function __construct(
        ContainerInterface $container
    ) {
        $this->logger = $container->get(Logger::class);
        $this->twig = $container->get(Twig::class);
    }

    public function getErrorHandler(): callable
    {
        return function (Throwable $exception, Inspector $inspector) {
            if ($exception instanceof HttpNotFoundException) {
                http_response_code(404);
                echo $this->twig->fetch('404.twig');
                die;
            }

            $title = $inspector->getExceptionName();
            $code = $exception->getCode();
            $message = $inspector->getExceptionMessage();

            $this->logger->error('Exception! ' . $title . '(' . $code . ') -> ' . $message);

            http_response_code(500);
            require __DIR__ . '/Templates/exception.php';
            die;

            // TODO add logger with mail
        };
    }
}
