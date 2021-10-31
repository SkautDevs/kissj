<?php

namespace kissj\Participant;

use kissj\Mailer\PhpMailerWrapper;
use kissj\Payment\Payment;
use kissj\Payment\PaymentService;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserService;

class ParticipantService {
    public function __construct(
        private ParticipantRepository $participantRepository,
        private PaymentService $paymentService,
        private UserRepository $userRepository,
        private UserService $userService,
        private PhpMailerWrapper $mailer,
    ) {
    }

    /**
     * @return Participant[]
     */
    public function getAllParticipantsWithStatus(string $role, string $status): array {
        if (!in_array($role, User::ROLES, true)) {
            throw new \RuntimeException('Unknown role: '.$role);
        }

        if (!in_array($status, User::STATUSES, true)) {
            throw new \RuntimeException('Unknown status: '.$status);
        }

        /** @var Participant[] $participants */
        $participants = $this->participantRepository->findBy(['role' => $role]);

        $participantsWithRole = [];
        foreach ($participants as $participant) {
            if ($participant->user->status === $status) {
                $participantsWithRole[] = $participant;
            }
        }

        return $participantsWithRole;
    }

    // TODO move into payment service, same as comfirmPayment
    public function cancelPayment(Payment $payment, string $reason): Payment {
        $this->paymentService->cancelPayment($payment);
        $this->userService->closeRegistration($payment->participant->user);

        $this->mailer->sendCancelledPayment($payment->participant, $reason);

        return $payment;
    }

    public function findParticipantFromUserMail(string $emailFrom): ?Participant {
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
        $this->userService->openRegistration($participant->user);

        return $participant;
    }
}
