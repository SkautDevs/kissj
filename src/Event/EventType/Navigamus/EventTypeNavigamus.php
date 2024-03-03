<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Navigamus;

use kissj\Application\DateTimeUtils;
use kissj\Event\ContentArbiterIst;
use kissj\Event\EventType\EventType;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Participant;
use kissj\Participant\Patrol\PatrolLeader;

class EventTypeNavigamus extends EventType
{
    protected function getPrice(Participant $participant): int
    {
        $now = DateTimeUtils::getDateTime();
        $patrolPrice = match (true) {
            $now < DateTimeUtils::getDateTime('2022-03-31 23:59:59') => 1100,
            $now < DateTimeUtils::getDateTime('2022-05-01 23:59:59') => 1150,
            $now < DateTimeUtils::getDateTime('2022-05-31 23:59:59') => 1200,
            default => 1200,
        };

        return match (true) {
            $participant instanceof Ist => 900,
            $participant instanceof PatrolLeader => (count($participant->patrolParticipants) + 1) * $patrolPrice,
            default => throw new \Exception('Unknown participant class'),
        };
    }

    public function getContentArbiterIst(): ContentArbiterIst
    {
        $caIst = parent::getContentArbiterIst();
        $caIst->skills = true;
        $caIst->tshirt = true;

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

    public function isUnlockExpiredButtonAllowed(): bool
    {
        return true;
    }
}
