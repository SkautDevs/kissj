<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Wsj;

use kissj\Event\EventType\EventType;

class EventTypeWsj extends EventType
{
    /**
     * @return array<string, string>
     */
    public function getTranslationFilePaths(): array
    {
        return [
            'en' => __DIR__ . '/en_wsj.yaml',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function getLanguages(): array
    {
        return [
            'en' => '🇬🇧 English',
        ];
    }
}
