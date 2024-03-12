<?php

declare(strict_types=1);

namespace kissj\Participant\Admin;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Mailer\Mailer;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Troop\TroopLeader;
use kissj\Participant\Troop\TroopParticipant;
use kissj\Payment\Payment;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentService;
use kissj\Payment\PaymentStatus;
use kissj\User\UserRepository;
use kissj\User\UserStatus;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class AdminService
{
    public function __construct(
        private UserRepository $userRepository,
        private ParticipantRepository $participantRepository,
        private PaymentRepository $paymentRepository,
        private PaymentService $paymentService,
        private Mailer $mailer,
        private TranslatorInterface $translator,
        private LoggerInterface $logger,
    ) {
    }

    public function isPaymentTransferPossible(
        ?Participant $participantFrom,
        ?Participant $participantTo,
        FlashMessagesInterface $flash // use for outputting friendly user messages what is wrong
    ): bool {
        $isPossible = true;

        if ($participantFrom === null || $participantTo === null) {
            $flash->warning($this->translator->trans('flash.warning.nullParticipants'));

            return false;
        }

        if ($participantFrom->id === $participantTo->id) {
            $flash->warning($this->translator->trans('flash.warning.differentParticipants'));
            return false;
        }

        if ($participantFrom->role !== $participantTo->role) {
            $flash->warning($this->translator->trans('flash.warning.sameRole'));
            $isPossible = false;
        }

        if (!$participantFrom->getUserButNotNull()->status->isPaidOrCancelled()) {
            $flash->warning($this->translator->trans('flash.warning.notPaid'));
            $isPossible = false;
        }

        if ($participantTo->getUserButNotNull()->status->isPaidOrCancelled()) {
            $flash->warning($this->translator->trans('flash.warning.isPaid'));
            $isPossible = false;
        }

        if ($participantTo instanceof TroopLeader && $participantTo->getTroopParticipantsCount() > 0) {
            $flash->warning($this->translator->trans('flash.warning.troopLeaderHasParticipants'));
            $isPossible = false;
        }

        if ($participantFrom instanceof PatrolLeader || $participantTo instanceof PatrolLeader) {
            $flash->warning($this->translator->trans('flash.warning.patrolLeaderNotSupported'));
            $isPossible = false;
        }

        // KORBO specific check
        if ($participantFrom->scarf !== $participantTo->scarf) {
            $flash->info($this->translator->trans('flash.info.differentScarfs'));
        }

        return $isPossible;
    }

    /**
     * Transfer payment From To
     * Cancel all waiting payments To and send email about it
     * Set From as open and send him email about payment transfer
     * Set To as paid and send him email about payment transfer
     * Handle scarf correction on To
     */
    public function transferPayment(Participant $participantFrom, Participant $participantTo): void
    {
        $transferredPayment = $this->handlePayments($participantFrom, $participantTo);

        foreach ($participantTo->payment as $payment) {
            if ($payment->status === PaymentStatus::Waiting) {
                $this->paymentService->cancelPayment($payment);
                $this->mailer->sendCancelledPayment(
                    $participantTo,
                    $this->translator->trans(
                        'email.text.paymentTransfered',
                        [],
                        null,
                        'cs'
                    ) // TODO add preference according to participant
                );
            }
        }

        if ($participantFrom->scarf !== $participantTo->scarf) {
            $participantTo->scarf = $participantFrom->scarf;
        }

        if (
            $participantFrom instanceof TroopParticipant
            && $participantTo instanceof TroopParticipant
        ) {
            $participantTo->troopLeader = $participantFrom->troopLeader;
            $participantFrom->troopLeader = null;
        }

        if (
            $participantFrom instanceof TroopLeader
            && $participantTo instanceof TroopLeader
        ) {
            if ($participantTo->getTroopParticipantsCount() > 0) {
                throw new \RuntimeException('Troop leader has participants');
            }

            foreach ($participantFrom->troopParticipants as $troopParticipant) {
                $troopParticipant->troopLeader = $participantTo;
                $this->participantRepository->persist($troopParticipant);
            }
        }

        $registrationPayDateFrom = $participantFrom->registrationPayDate;
        $participantFrom->registrationPayDate = $participantTo->registrationPayDate;
        $participantTo->registrationPayDate = $registrationPayDateFrom;

        $userFrom = $participantFrom->getUserButNotNull();
        $userFrom->status = UserStatus::Open;

        $userTo = $participantTo->getUserButNotNull();
        $userTo->status = UserStatus::Paid;

        $this->participantRepository->persist($participantFrom);
        $this->participantRepository->persist($participantTo);
        $this->userRepository->persist($userFrom);
        $this->userRepository->persist($userTo);

        $this->mailer->sendRegistrationPaid($participantTo);
        $this->mailer->sendPaymentTransferedFromYou($participantFrom);

        $this->logger->info(sprintf(
            'Transferred payment ID %s from participant ID %s to participant ID %s',
            $transferredPayment?->id ?? 'N/A',
            $userFrom->id,
            $userTo->id,
        ));
    }

    private function handlePayments(
        Participant $participantFrom,
        Participant $participantTo,
    ): ?Payment {
        if ($participantFrom instanceof TroopParticipant) {
            // Troop Participant has no payments by itself, it is handled by Troop Leader
            return null;
        }

        $correctPayment = $participantFrom->getFirstPaidPayment();
        if ($correctPayment === null) {
            throw new \RuntimeException('Payment marked as paid was not found with participant marked as paid');
        }

        $correctPayment->participant = $participantTo;
        $this->paymentRepository->persist($correctPayment);

        return $correctPayment;
    }
}
