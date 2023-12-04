<?php

declare(strict_types=1);

namespace kissj\Skautis;

use Skautis\Config;
use Skautis\SessionAdapter\SessionAdapter;
use Skautis\Skautis;
use Skautis\User;
use Skautis\Wsdl\WebServiceFactory;
use Skautis\Wsdl\WsdlManager;

class SkautisFactory
{
    public function __construct(
        private readonly string $appId,
        private readonly bool $isTestMode,
    ) {
    }

    public function getSkautis(): Skautis
    {
        $config = new Config($this->appId, $this->isTestMode, true, true);
        $wsdlManager = new WsdlManager(new WebServiceFactory(), $config);
        $user = new User($wsdlManager, new SessionAdapter());

        return new Skautis($wsdlManager, $user);
    }
}
