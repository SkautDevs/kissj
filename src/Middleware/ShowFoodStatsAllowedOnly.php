<?php

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;

class ShowFoodStatsAllowedOnly extends BaseMiddleware
{
    public function __construct(
        private readonly FlashMessagesInterface $flashMessages,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $eventType = $this->tryGetEvent($request)?->getEventType()->showFoodStats();

        if ($eventType === false) {
            $this->flashMessages->warning(
                'flash.warning.food-stats-not-allowed'
            );

            return $this->createRedirectResponse($request, 'admin-dashboard');
        }

        return $handler->handle($request);
    }
}
