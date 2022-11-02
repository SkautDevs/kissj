<?php

declare(strict_types=1);

namespace kissj\Participant\Ist;

use kissj\Event\Event;
use kissj\Participant\Admin\StatisticValueObject;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\User\User;
use kissj\User\UserStatus;

class IstService
{
    public function __construct(
        private readonly ParticipantRepository $participantRepository,
    ) {
    }

    public function getAllIstsStatistics(Event $event, User $admin): StatisticValueObject
    {
        $ists = $this->participantRepository->getAllParticipantsWithStatus(
            [ParticipantRole::Ist],
            UserStatus::cases(),
            $event,
            $admin,
        );

        return new StatisticValueObject($ists);
    }
}
