<?php

declare(strict_types=1);

namespace kissj\Participant\Troop;

use kissj\Event\Event;
use kissj\Participant\Admin\StatisticValueObject;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\User\User;
use kissj\User\UserStatus;

class TroopService
{
    public function __construct(
        private readonly ParticipantRepository $participantRepository,
    ) {
    }

    public function getAllTroopLeaderStatistics(Event $event, User $admin): StatisticValueObject
    {
        $troopLeaders = $this->participantRepository->getAllParticipantsWithStatus(
            [ParticipantRole::TroopLeader],
            UserStatus::cases(),
            $event,
            $admin,
        );

        return new StatisticValueObject($troopLeaders);
    }

    public function getAllTroopParticipantStatistics(Event $event, User $admin): StatisticValueObject
    {
        $troopLeaders = $this->participantRepository->getAllParticipantsWithStatus(
            [ParticipantRole::TroopParticipant],
            UserStatus::cases(),
            $event,
            $admin,
        );

        return new StatisticValueObject($troopLeaders);
    }
}
