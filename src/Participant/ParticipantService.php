<?php

namespace kissj\Participant;

use kissj\Event\Event;
use kissj\Mailer\PhpMailerWrapper;
use kissj\Payment\Payment;
use kissj\Payment\PaymentService;
use kissj\User\User;
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

    /**
     * TODO optimalize
     *
     * @param string[] $roles
     * @param string[] $statuses
     * @param Event    $event
     * @param User     $adminUser
     * @return Participant[]
     */
    public function getAllParticipantsWithStatus(
        array $roles,
        array $statuses,
        Event $event,
        User $adminUser,
    ): array {
        /** @var Participant[] $participants */
        $participants = $this->participantRepository->findAll();
        $participants = $this->filterContingentAdminParticipants($participants, $adminUser);

        $validParticipants = [];
        foreach ($participants as $participant) {
            $user = $participant->getUserButNotNull();
            if (
                $user->event->id === $event->id
                && in_array($user->role, $roles, true)
                && in_array($user->status, $statuses, true)
            ) {
                $validParticipants[$participant->id] = $participant;
            }
        }

        return $validParticipants;
    }

    /**
     * @param Participant[] $participants
     * @param User          $adminUser
     * @return Participant[]
     */
    public function filterContingentAdminParticipants(array $participants, User $adminUser): array
    {
        return match ($adminUser->role) {
            User::ROLE_ADMIN => $participants,
            User::ROLE_CONTINGENT_ADMIN_CS => array_filter($participants, function (Participant $p): bool {
                return $p->contingent === 'detail.contingent.czechia';
            }),
            User::ROLE_CONTINGENT_ADMIN_SK => array_filter($participants, function (Participant $p): bool {
                return $p->contingent === 'detail.contingent.slovakia';
            }),
            User::ROLE_CONTINGENT_ADMIN_PL => array_filter($participants, function (Participant $p): bool {
                return $p->contingent === 'detail.contingent.poland';
            }),
            User::ROLE_CONTINGENT_ADMIN_HU => array_filter($participants, function (Participant $p): bool {
                return $p->contingent === 'detail.contingent.hungary';
            }),
            User::ROLE_CONTINGENT_ADMIN_EU => array_filter($participants, function (Participant $p): bool {
                return $p->contingent === 'detail.contingent.european';
            }),
            default => [],
        };
    }

    // TODO move into payment service, same as comfirmPayment
    public function cancelPayment(Payment $payment, string $reason): Payment
    {
        $this->paymentService->cancelPayment($payment);
        $this->userService->closeRegistration($payment->participant->user);

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
        $this->userService->openRegistration($participant->user);

        return $participant;
    }
}
