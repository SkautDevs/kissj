<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Programme\Device\DevicePairingService;
use kissj\Programme\Device\DeviceSession;
use kissj\Programme\Device\DeviceSessionRepository;
use LogicException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;

class DeviceSessionMiddleware extends BaseMiddleware
{
    public const string HEADER = 'X-Kissj-Device-Session';

    public function __construct(
        private readonly DeviceSessionRepository $deviceSessionRepository,
        private readonly DevicePairingService $devicePairingService,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $token = $request->getHeaderLine(self::HEADER);
        if ($token === '') {
            return $this->createUnauthorizedResponse();
        }

        $session = $this->deviceSessionRepository->findByTokenHash(DeviceSession::hashToken($token));
        $authorizedEvent = $request->getAttribute('authorizedEvent');
        if ($authorizedEvent instanceof Event === false) {
            throw new LogicException('Device session middleware runs without authorized event');
        }

        $now = DateTimeUtils::getDateTime();
        if ($session === null || $session->isExpired($now)) {
            return $this->createUnauthorizedResponse();
        }

        $user = $session->participant->getUserButNotNull();
        $isAllowed = $this->devicePairingService->isDeviceAccessAllowed($user);
        if ($user->event->id !== $authorizedEvent->id || $isAllowed === false) {
            return $this->createUnauthorizedResponse();
        }

        if ($session->isExtensionDue($now)) {
            $session->extend($now);
            $this->deviceSessionRepository->persist($session);
        }

        return $handler->handle($request->withAttribute('sessionParticipant', $session->participant));
    }
}
