<?php

declare(strict_types=1);

namespace kissj\Participant\Troop;

use kissj\Event\Event;
use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Participant\ParticipantRole;
use kissj\Payment\Payment;
use kissj\Payment\PaymentRepository;
use kissj\User\UserStatus;
use LogicException;

readonly class TroopService
{
    public function __construct(
        private TroopLeaderRepository $troopLeaderRepository,
        private TroopParticipantRepository $troopParticipantRepository,
        private PaymentRepository $paymentRepository,
        private FlashMessagesInterface $flashMessages,
    ) {
    }

    public function tieTroopParticipantToTroopLeader(
        TroopParticipant $troopParticipant,
        TroopLeader $troopLeader,
    ): TroopParticipant {
        if (
            $troopLeader->getUserButNotNull()->status !== UserStatus::Open
        ) {
            $this->flashMessages->warning('flash.warning.troopLeaderNotOpen');

            return $troopParticipant;
        }

        if ($troopParticipant->troopLeader?->id === $troopLeader->id) {
            $this->flashMessages->warning('flash.warning.troopParticipantAlreadyTied');

            return $troopParticipant;
        }

        $troopParticipant->troopLeader = $troopLeader;
        $this->troopParticipantRepository->persist($troopParticipant);
        $this->flashMessages->success('flash.success.troopParticipantTiedToTroopLeader');

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
            $this->flashMessages->warning('flash.warning.troopLeaderNotFoundTieNotPossible');
            $valid = false;
        }

        if ($tl !== null && $tl->getUserButNotNull()->status->isPaidOrCancelled()) {
            $this->flashMessages->warning('flash.warning.troopLeaderPaidTieNotPossible');
            $valid = false;
        }

        $tp = $this->troopParticipantRepository->findTroopParticipantFromTieCode($troopParticipantCode, $event);
        if ($tp === null) {
            $this->flashMessages->warning('flash.warning.troopParticipantNotFoundTieNotPossible');
            $valid = false;
        }

        if ($tp !== null && $tp->getUserButNotNull()->status->isPaidOrCancelled()) {
            $this->flashMessages->warning('flash.warning.troopParticipantPaidTieNotPossible');
            $valid = false;
        }

        if ($tp !== null && $tp->troopLeader !== null) {
            $this->flashMessages->warning('flash.warning.troopParticipantHasTroopTieNotPossible');
            $valid = false;
        }

        if ($valid && $tp !== null && $tl !== null) {
            $tp->troopLeader = $tl;
            $this->troopParticipantRepository->persist($tp);
            $this->flashMessages->success('flash.success.troopTied');
        }

        return $valid;
    }

    public function tryUntieWithMessages(string $troopParticipantCode, Event $event): bool
    {
        $valid = true;
        $tp = $this->troopParticipantRepository->findTroopParticipantFromTieCode($troopParticipantCode, $event);
        if ($tp === null) {
            $this->flashMessages->warning('flash.warning.troopParticipantNotFoundUntieNotPossible');
            $valid = false;
        }

        if ($tp !== null && $tp->getUserButNotNull()->status->isPaidOrCancelled()) {
            $this->flashMessages->warning('flash.warning.troopParticipantPaidUntieNotPossible');
            $valid = false;
        }

        if ($valid && $tp !== null) {
            $tp->troopLeader = null;
            $this->troopParticipantRepository->persist($tp);
            $this->flashMessages->success('flash.success.participantUntied');
        }

        return $valid;
    }

    public function swapTroopLeaderWithParticipant(TroopParticipant $troopParticipant): void
    {
        $troopLeader = $troopParticipant->troopLeader;
        if ($troopLeader === null) {
            throw new LogicException('Cannot swap: participant has no troop leader');
        }

        if ($troopLeader->getUserButNotNull()->status !== $troopParticipant->getUserButNotNull()->status) {
            $this->flashMessages->warning('flash.warning.troopSwapStatusMismatch');

            return;
        }

        // collect data before mutations, exclude the participant being promoted
        $existingParticipants = array_filter(
            $troopLeader->troopParticipants,
            fn (TroopParticipant $tp) => $tp->id !== $troopParticipant->id,
        );
        $oldLeaderPayments = $this->paymentRepository->findByParticipant($troopLeader);
        $newLeaderPayments = $this->paymentRepository->findByParticipant($troopParticipant);

        $this->troopLeaderRepository->transactional(
            fn () => $this->executeSwap(
                $troopParticipant,
                $troopLeader,
                $existingParticipants,
                $oldLeaderPayments,
                $newLeaderPayments,
            ),
        );

        $this->flashMessages->success('flash.success.troopLeaderSwapped');
    }

    /**
     * @param TroopParticipant[] $existingParticipants
     * @param Payment[] $oldLeaderPayments
     * @param Payment[] $newLeaderPayments
     */
    private function executeSwap(
        TroopParticipant $troopParticipant,
        TroopLeader $troopLeader,
        array $existingParticipants,
        array $oldLeaderPayments,
        array $newLeaderPayments,
    ): void {
        $troopParticipantId = $troopParticipant->id;
        $troopLeaderId = $troopLeader->id;

        // Role mutation via cross-type persist is safe here: LeanMapper's persist only writes
        // modified columns (role, patrolName, troopLeader) and does not validate entity class vs role
        $troopParticipant->troopLeader = null;
        $troopParticipant->role = ParticipantRole::TroopLeader;
        $troopParticipant->patrolName = $troopLeader->patrolName;
        $this->troopParticipantRepository->persist($troopParticipant);

        $troopLeader->role = ParticipantRole::TroopParticipant;
        $troopLeader->patrolName = null;
        $this->troopLeaderRepository->persist($troopLeader);

        // refetch to get correct entity types and avoid fail ORM typecheck
        $newLeader = $this->troopLeaderRepository->get($troopParticipantId);
        if (!$newLeader instanceof TroopLeader) {
            throw new LogicException('refetched entity is not TroopLeader after role mutation');
        }
        $demotedParticipant = $this->troopParticipantRepository->get($troopLeaderId);
        if (!$demotedParticipant instanceof TroopParticipant) {
            throw new LogicException('refetched entity is not TroopParticipant after role mutation');
        }

        // switch participants to new leader
        foreach ($existingParticipants as $tp) {
            $tp->troopLeader = $newLeader;
            $this->troopParticipantRepository->persist($tp);
        }

        // switch old leader to new leader
        $demotedParticipant->troopLeader = $newLeader;
        $this->troopParticipantRepository->persist($demotedParticipant);

        // switch payments to new leader
        foreach ($oldLeaderPayments as $payment) {
            $payment->participant = $newLeader;
            $this->paymentRepository->persist($payment);
        }
        foreach ($newLeaderPayments as $payment) {
            $payment->participant = $demotedParticipant;
            $this->paymentRepository->persist($payment);
        }
    }
}
