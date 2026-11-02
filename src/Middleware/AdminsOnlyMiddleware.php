<?php

namespace kissj\Middleware;

use kissj\Authorization\AdminRouteAccessResolver;
use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Participant\ParticipantRepository;
use kissj\Payment\PaymentRepository;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Slim\Interfaces\RouteInterface;
use Slim\Routing\RouteContext;

class AdminsOnlyMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly FlashMessagesInterface $flashMessages,
        private readonly AdminRouteAccessResolver $routeAccessResolver,
        private readonly ParticipantRepository $participantRepository,
        private readonly PaymentRepository $paymentRepository,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $user = $this->tryGetUser($request);

        if ($user !== null && $user->isAdmin() === false) {
            $this->flashMessages->error('flash.error.adminOnly');

            return $this->createRedirectResponse($request, 'loginAskEmail');
        }

        if ($user === null) {
            return $handler->handle($request);
        }

        $route = RouteContext::fromRequest($request)->getRoute();
        if (
            $route === null
            || $this->routeAccessResolver->allowsRoute($user, $route) === false
            || $this->isParticipantInScope($route, $user) === false
        ) {
            $this->flashMessages->error('flash.error.adminOnly');

            return $this->createRedirectResponse($request, 'admin-dashboard');
        }

        return $handler->handle($request);
    }

    private function isParticipantInScope(RouteInterface $route, User $user): bool
    {
        $participantId = $route->getArgument('participantId');
        if ($participantId !== null) {
            return $this->participantRepository->isParticipantVisibleToAdmin((int)$participantId, $user);
        }

        $paymentId = $route->getArgument('paymentId');
        if ($paymentId !== null) {
            return $this->participantRepository->isParticipantVisibleToAdmin(
                $this->paymentRepository->getById((int)$paymentId, $user->event)->participant->id,
                $user,
            );
        }

        return true;
    }
}
