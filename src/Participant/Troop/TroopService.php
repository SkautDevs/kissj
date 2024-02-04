<?php

declare(strict_types=1);

namespace kissj\Participant\Troop;

use kissj\Event\Event;
use kissj\FlashMessages\FlashMessagesInterface;
use kissj\User\UserStatus;
use Symfony\Contracts\Translation\TranslatorInterface;

class TroopService
{
    public function __construct(
        private readonly TroopLeaderRepository $troopLeaderRepository,
        private readonly TroopParticipantRepository $troopParticipantRepository,
        private readonly FlashMessagesInterface $flashMessages,
        private readonly TranslatorInterface $translator,
    ) {
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
        return $troopParticipant->troopLeader?->id === $troopLeader->id;
    }

    public function untieTroopParticipant(int $participantId): TroopParticipant
    {
        $troopParticipant = $this->troopParticipantRepository->get($participantId);
        $troopParticipant->troopLeader = null;
        $this->troopParticipantRepository->persist($troopParticipant);

        return $troopParticipant;
    }

    public function tryTieTogetherWithMessages(
        string $troopLeaderCode,
        string $troopParticipantCode,
        Event $event,
    ): bool {
        $valid = true;
        $tl = $this->troopLeaderRepository->findTroopLeaderFromTieCode($troopLeaderCode, $event);
        if ($tl === null) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.troopLeaderNotFoundTieNotPossible'));
            $valid = false;
        }

        if ($tl !== null && $tl->getUserButNotNull()->status === UserStatus::Paid) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.troopLeaderPaidTieNotPossible'));
            $valid = false;
        }

        $tp = $this->troopParticipantRepository->findTroopParticipantFromTieCode($troopParticipantCode, $event);
        if ($tp === null) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.troopParticipantNotFoundTieNotPossible'));
            $valid = false;
        }

        if ($tp !== null && $tp->getUserButNotNull()->status === UserStatus::Paid) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.troopParticipantPaidTieNotPossible'));
            $valid = false;
        }

        if ($tp !== null && $tp->troopLeader !== null) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.troopParticipantHasTroopTieNotPossible'));
            $valid = false;
        }

        if ($valid && $tp !== null && $tl !== null) {
            $tp->troopLeader = $tl;
            $this->troopParticipantRepository->persist($tp);
            $this->flashMessages->success($this->translator->trans('flash.success.troopTied'));
        }

        return $valid;
    }

    public function tryUntieWithMessages(string $troopParticipantCode, Event $event): bool
    {
        $valid = true;
        $tp = $this->troopParticipantRepository->findTroopParticipantFromTieCode($troopParticipantCode, $event);
        if ($tp === null) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.troopParticipantNotFoundUntieNotPossible'));
            $valid = false;
        }

        if ($tp !== null && $tp->getUserButNotNull()->status === UserStatus::Paid) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.troopParticipantPaidUntieNotPossible'));
            $valid = false;
        }

        if ($valid && $tp !== null) {
            $tp->troopLeader = null;
            $this->troopParticipantRepository->persist($tp);
            $this->flashMessages->success($this->translator->trans('flash.success.participantUntied'));
        }

        return $valid;
    }
}
