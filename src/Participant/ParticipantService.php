<?php declare(strict_types=1);

namespace kissj\Participant;

use kissj\Mailer\PhpMailerWrapper;
use kissj\Payment\Payment;
use kissj\Payment\PaymentService;
use kissj\User\UserRepository;
use kissj\User\UserService;

class ParticipantService
{
    public function __construct(
        private ParticipantRepository $participantRepository,
        private PaymentService $paymentService,
        private UserRepository $userRepository,
        private UserService $userService,
        private PhpMailerWrapper $mailer,
    ) {
    }

    // TODO move into payment service, same as comfirmPayment
    public function cancelPayment(Payment $payment, string $reason): Payment
    {
        $this->paymentService->cancelPayment($payment);
        $this->userService->closeRegistration($payment->participant->getUserButNotNull());

        $this->mailer->sendCancelledPayment($payment->participant, $reason);

        return $payment;
    }

    public function findParticipantFromUserMail(string $emailFrom): ?Participant
    {
        // TODO optimalize into one query with join
        $user = $this->userRepository->findBy(['email' => $emailFrom]);
        if (count($user) === 0) {
            return null;
        }

        $participant = $this->participantRepository->findBy(['user_id' => $user[0]->id]);
        if (count($participant) === 0) {
            return null;
        }

        return $participant[0];
    }

    public function denyRegistration(Participant $participant, string $reason): Participant
    {
        $this->mailer->sendDeniedRegistration($participant, $reason);
        $this->userService->openRegistration($participant->getUserButNotNull());

        return $participant;
    }

    public function approveRegistration(Participant $participant): Participant
    {
        $payment = $this->paymentService->createAndPersistNewPayment($participant);

        if ($participant->isInSpecialPaymentContingent()) {
            $this->mailer->sendRegistrationApprovedForForeignContingents($participant);
        } else {
            $this->mailer->sendRegistrationApprovedWithPayment($participant, $payment);
        }
        $this->userService->approveRegistration($participant->getUserButNotNull());

        return $participant;
    }
}
