<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Obrok;

use kissj\Event\EventType\EventType;

class EventTypeObrok extends EventType
{
    /**
     * @return array<string, string>
     */
    public function getTranslationFilePaths(): array
    {
        return [
            'cs' => __DIR__ . '/cs_obrok.yaml',
        ];
    }

    public function isUnlockExpiredButtonAllowed(): bool
    {
        return true;
    }
}
