<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Ospz;

use kissj\Event\ContentArbiterIst;
use kissj\Event\ContentArbiterPatrolLeader;
use kissj\Event\ContentArbiter\ContentArbiterItem;
use kissj\Event\EventType\EventType;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRole;
use kissj\Participant\Patrol\PatrolLeader;

class EventTypeOspz extends EventType
{
    public function getPrice(Participant $participant): int
    {
        return match (true) {
            $participant instanceof Ist => 200,
            $participant instanceof PatrolLeader => ($participant->getPatrolParticipantsCount() + 1) * 400,
            default => parent::getPrice($participant),
        };
    }

    #[\Override]
    public function getContentArbiterIst(): ContentArbiterIst
    {
        $caIst = parent::getContentArbiterIst();
        $caIst->food->allowed = true;
        $caIst->food->options = ContentArbiterItem::selfMappedOptions($this->getFoodOptions());

        return $caIst;
    }

    #[\Override]
    public function getContentArbiterPatrolLeader(): ContentArbiterPatrolLeader
    {
        $caPl = parent::getContentArbiterPatrolLeader();
        $caPl->contingent->allowed = true;
        $caPl->contingent->options = ContentArbiterItem::selfMappedOptions($this->getContingents());

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

    #[\Override]
    protected function isReceiptAllowed(): bool
    {
        return true;
    }
}
