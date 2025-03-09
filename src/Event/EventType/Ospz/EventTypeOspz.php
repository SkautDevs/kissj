<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Ospz;

use kissj\Event\ContentArbiterIst;
use kissj\Event\ContentArbiterPatrolLeader;
use kissj\Event\EventType\EventType;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRole;

class EventTypeOspz extends EventType
{
    protected function getPrice(Participant $participant): int
    {
        return match ($participant->role) {
            ParticipantRole::Ist => 200,
            default => 400,
        };
    }

    public function getContentArbiterIst(): ContentArbiterIst
    {
        $caIst = parent::getContentArbiterIst();
        $caIst->food = true;

        return $caIst;
    }

    public function getContentArbiterPatrolLeader(): ContentArbiterPatrolLeader
    {
        $caPl = parent::getContentArbiterPatrolLeader();
        $caPl->contingent = true;

        return $caPl;
    }

    /**
     * @inheritDoc
     */
    public function getTranslationFilePaths(): array
    {
        return [
            'cs' => __DIR__ . '/cs_ospz.yaml',
        ];
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getLanguages(): array
    {
        return [
            'cs' => '🇨🇿 Česky',
        ];
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getContingents(): array
    {
        return [
            'detail.contingent.racer',
            'detail.contingent.watcher',
        ];
    }

    public function isLoginSkautisAllowed(): bool
    {
        return true;
    }
}
