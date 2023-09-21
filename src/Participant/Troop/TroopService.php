<?php

declare(strict_types=1);

namespace kissj\Participant\Troop;

use kissj\Event\Event;
use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Participant\Admin\StatisticValueObject;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\User\User;
use kissj\User\UserStatus;
use Symfony\Contracts\Translation\TranslatorInterface;

class TroopService
{
    public function __construct(
        private readonly TroopParticipantRepository $troopParticipantRepository,
        private readonly ParticipantRepository $participantRepository,
        private readonly FlashMessagesInterface $flashMessages,
        private readonly TranslatorInterface $translator,
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

    public function tieTroopParticipantToTroopLeader(
        TroopParticipant $troopParticipant,
        TroopLeader $troopLeader,
    ): TroopParticipant {
        if (
            $troopLeader->getUserButNotNull()->status !== UserStatus::Open
        ) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.troopLeaderNotOpen'));

            return $troopParticipant;
        }
        
        if ($troopParticipant->troopLeader?->id === $troopLeader->id) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.troopParticipantAlreadyTied'));

            return $troopParticipant;
        }

        $troopParticipant->troopLeader = $troopLeader;
        $this->troopParticipantRepository->persist($troopParticipant);
        $this->flashMessages->success($this->translator->trans('flash.success.troopParticipantTiedToTroopLeader'));

        return $troopParticipant;
    }

    public function troopParticipantBelongsTroopLeader(
        TroopParticipant $troopParticipant,
        TroopLeader $troopLeader
    ): bool {
        return $troopParticipant->troopLeader->id === $troopLeader->id;
    }

    public function untieTroopParticipant(int $participantId): TroopParticipant
    {
        $troopParticipant = $this->troopParticipantRepository->get($participantId);
        $troopParticipant->troopLeader = null;
        $this->troopParticipantRepository->persist($troopParticipant);
        
        return $troopParticipant;   
    }
}
