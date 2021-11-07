<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Navigamus;

use kissj\Event\ContentArbiterIst;
use kissj\Event\EventType\EventType;

class EventTypeNavigamus extends EventType
{
    public function getContentArbiterIst(): ContentArbiterIst
    {
        $caIst = parent::getContentArbiterIst();
        $caIst->skills = true;

        return $caIst;
    }

    /**
     * @return array<string, string>
     */
    public function getTranslationFilePaths(): array
    {
        return [
            'cs' => __DIR__ . '/cs_navigamus.yaml',
        ];
    }
}
