<?php

declare(strict_types=1);

namespace kissj\Middleware;

use Override;

class DevicePairingCsrfMiddleware extends CsrfMiddleware
{
    #[Override]
    protected function getFailureRedirectRouteName(): string
    {
        return 'showDevicePairing';
    }
}
