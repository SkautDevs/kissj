<?php

declare(strict_types=1);

namespace kissj\Application;

use kissj\AbstractController;
use kissj\Event\EventRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Throwable;

class HealthController extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
    ) {
    }

    public function check(Response $response): Response
    {
        try {
            $this->eventRepository->countBy([]);
        } catch (Throwable $throwable) {
            $this->logger->error('Healthcheck database ping failed', ['exception' => $throwable]);

            return $this->getResponseWithJson($response, ['status' => 'error'], 500);
        }

        return $this->getResponseWithJson($response, ['status' => 'ok']);
    }
}
