<?php

namespace kissj\Participant\Admin;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Mailer\PhpMailerWrapper;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Payment\Payment;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentService;
use kissj\User\User;
use kissj\User\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminService {
    private $userRepository;
    private $participantRepository;
    private $paymentService;
    private $mailer;
    private $translator;
    private $paymentRepository;
    private $logger;

    public function __construct(
        UserRepository $userRepository,
        ParticipantRepository $participantRepository,
        PaymentRepository $paymentRepository,
        PaymentService $paymentService,
        PhpMailerWrapper $mailer,
        TranslatorInterface $translator,
        LoggerInterface $logger
    ) {
        $this->userRepository = $userRepository;
        $this->participantRepository = $participantRepository;
        $this->paymentRepository = $paymentRepository;
        $this->paymentService = $paymentService;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    public function isPaymentTransferPossible(
        ?Participant $participantFrom,
        ?Participant $participantTo,
        FlashMessagesInterface $flash // use for outputting friendly user messages what is wrong
    ): bool {
        $isPossible = true;

        if ($participantFrom === null || $participantTo === null) {
            $flash->warning($this->translator->trans('flash.warning.nullParticipants'));

            return false; // no point to compare futher
        }

        if (!$participantFrom instanceof $participantTo) {
            $flash->warning($this->translator->trans('flash.warning.differentParticipants'));
            $isPossible = false;
        }

        if ($participantFrom->user->status !== User::STATUS_PAID) {
            $flash->warning($this->translator->trans('flash.warning.notPaid'));
            $isPossible = false;
        }

        if ($participantTo->user->status === User::STATUS_PAID) {
            $flash->warning($this->translator->trans('flash.warning.isPaid'));
            $isPossible = false;
        }

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
     *
     * @param Participant $participantFrom
     * @param Participant $participantTo
     */
    public function transferPayment(Participant $participantFrom, Participant $participantTo) {
        $correctPayment = null;
        foreach ($participantFrom->payment as $payment) {
            if ($payment->status === Payment::STATUS_PAID) {
                $correctPayment = $payment;
            }
        }

        if ($correctPayment === null) {
            throw new \RuntimeException('Payment marked as paid was not found with participant marked as paid');
        }

        foreach ($participantTo->payment as $payment) {
            if ($payment->status === Payment::STATUS_WAITING) {
                $this->paymentService->cancelPayment($payment);
                $this->mailer->sendCancelledPayment(
                    $participantTo,
                    $this->translator->trans('email.text.paymentTransfered', [], null, 'cs') // TODO add preference according to participant
                );
            }
        }

        // handle scarf correction
        if ($participantFrom->scarf !== $participantTo->scarf) {
            $participantTo->scarf = $participantFrom->scarf;
        }

        $correctPayment->participant = $participantTo;

        $userFrom = $participantFrom->user;
        $userFrom->status = User::STATUS_OPEN;

        $userTo = $participantTo->user;
        $userTo->status = User::STATUS_PAID;

        $this->paymentRepository->persist($correctPayment);
        $this->participantRepository->persist($participantTo);
        $this->userRepository->persist($userFrom);
        $this->userRepository->persist($userTo);

        $this->mailer->sendRegistrationPaid($participantTo);
        $this->mailer->sendPaymentTransferedFromYou($participantFrom);

        $this->logger->info('Tranfered payment ID '.$correctPayment->id
            .' from participant ID '.$userFrom->id.' to participant ID '.$userTo->id);
    }
}
