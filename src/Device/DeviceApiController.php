<?php

declare(strict_types=1);

namespace kissj\Device;

use kissj\AbstractController;
use kissj\Application\DateTimeUtils;
use kissj\Device\DTO\DeviceApiOkDTO;
use kissj\Device\DTO\DeviceApiPairingApprovedDTO;
use kissj\Device\DTO\DeviceApiPairingStartDTO;
use kissj\Device\DTO\DeviceApiPairingStatusDTO;
use kissj\Event\Event;
use LogicException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DeviceApiController extends AbstractController
{
    public function __construct(
        private readonly DevicePairingService $devicePairingService,
    ) {
    }

    public function pairingStart(Request $request, Response $response, Event $authorizedEvent): Response
    {
        $deviceId = $this->getParsedJsonFromBody($request)['deviceId'] ?? null;
        if (is_string($deviceId) === false || preg_match('/^[0-9a-f]{64}$/D', $deviceId) !== 1) {
            $response->getBody()->write('Invalid deviceId');

            return $response->withStatus(400)->withHeader('Content-Type', 'text/plain');
        }

        $pairing = $this->devicePairingService->start($authorizedEvent, $deviceId);
        if ($pairing === null) {
            $response->getBody()->write('Too many pending pairings');

            return $response->withStatus(429)->withHeader('Content-Type', 'text/plain');
        }

        $verificationUrl = $this->getRouter($request)->fullUrlFor(
            $request->getUri(),
            'dashboard',
            ['eventSlug' => $authorizedEvent->slug],
        ) . '#devicePairing';

        return $this->getResponseWithJson($response, new DeviceApiPairingStartDTO(
            $pairing->deviceCode,
            $pairing->userCode,
            $verificationUrl,
            DateTimeUtils::formatAtom($pairing->expiresAt),
            DevicePairing::POLL_INTERVAL_SECONDS,
        ));
    }

    public function pairingPoll(Request $request, Response $response, Event $authorizedEvent): Response
    {
        $deviceCode = $this->getParsedJsonFromBody($request)['deviceCode'] ?? null;
        $result = $this->devicePairingService->poll(is_string($deviceCode) ? $deviceCode : '', $authorizedEvent);

        if ($result->status !== DevicePairingPollStatus::Approved) {
            return $this->getResponseWithJson($response, new DeviceApiPairingStatusDTO($result->status->value));
        }

        return $this->getResponseWithJson($response, new DeviceApiPairingApprovedDTO(
            $result->status->value,
            $result->sessionToken ?? throw new LogicException('Approved poll result has no session token'),
            DateTimeUtils::formatAtom(
                $result->sessionExpiresAt ?? throw new LogicException('Approved poll result has no expiry'),
            ),
        ));
    }

    public function revokeSession(Request $request, Response $response): Response
    {
        $session = $request->getAttribute('deviceSession');
        if ($session instanceof DeviceSession === false) {
            throw new LogicException('Session revoke runs without device session');
        }

        $this->devicePairingService->revokeSession($session);

        return $this->getResponseWithJson($response, new DeviceApiOkDTO(true));
    }
}
