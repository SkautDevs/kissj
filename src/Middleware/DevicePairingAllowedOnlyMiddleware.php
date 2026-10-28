<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\Programme\Device\DevicePairingService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Slim\Exception\HttpNotFoundException;

class DevicePairingAllowedOnlyMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly DevicePairingService $devicePairingService,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $user = $this->tryGetUser($request);
        if ($user === null || $this->devicePairingService->isDeviceAccessAllowed($user) === false) {
            throw new HttpNotFoundException($request);
        }

        return $handler->handle($request);
    }
}
