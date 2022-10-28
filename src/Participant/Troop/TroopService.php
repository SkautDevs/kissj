<?php

declare(strict_types=1);

namespace kissj\Participant\Troop;

use kissj\Event\Event;
use kissj\Participant\Admin\StatisticValueObject;
use kissj\Participant\ParticipantRepository;
use kissj\User\User;
use kissj\User\UserStatus;

class TroopService
{
    public function __construct(
        private TroopLeaderRepository $troopLeaderRepository,
        private TroopParticipantRepository $troopParticipantRepository,
        private ParticipantRepository $participantRepository,
    ) {
    }

    public function getTroopLeader(User $user): TroopLeader
    {
        $troopLeader = $this->troopLeaderRepository->findOneBy(['user' => $user]);

        if ($troopLeader === null) {
            $troopLeader = new TroopLeader();
            $troopLeader->user = $user;
            $this->troopLeaderRepository->persist($troopLeader);
        }

        return $troopLeader;
    }

    public function getTroopParticipant(User $user): TroopParticipant
    {
        $troopParticipant = $this->troopParticipantRepository->findOneBy(['user' => $user]);

        if ($troopParticipant === null) {
            $troopParticipant = new TroopParticipant();
            $troopParticipant->user = $user;
            $this->troopParticipantRepository->persist($troopParticipant);
        }

        return $troopParticipant;
    }

    public function getAllTroopLeaderStatistics(Event $event, User $admin): StatisticValueObject
    {
        $troopLeaders = $this->participantRepository->getAllParticipantsWithStatus(
            [User::ROLE_TROOP_LEADER],
            UserStatus::cases(),
            $event,
            $admin,
        );

        return new StatisticValueObject($troopLeaders);
    }

    public function getAllTroopParticipantStatistics(Event $event, User $admin): StatisticValueObject
    {
        $troopLeaders = $this->participantRepository->getAllParticipantsWithStatus(
            [User::ROLE_TROOP_PARTICIPANT],
            UserStatus::cases(),
            $event,
            $admin,
        );

        return new StatisticValueObject($troopLeaders);
    }
}
