<?php

declare(strict_types=1);

namespace kissj\Skautis;

class SkautisService
{
    public function __construct(
        private readonly SkautisFactory $skautisFactory,
    ) {
    }
    
    public function getLoginUri(string $backlink): string
    {
        return $this->skautisFactory->getSkautis()->getLoginUrl($backlink);
    }
}
