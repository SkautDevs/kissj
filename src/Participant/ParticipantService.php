<?php declare(strict_types=1);

namespace kissj\Participant;

use kissj\AbstractService;
use kissj\Mailer\PhpMailerWrapper;
use kissj\Payment\Payment;
use kissj\Payment\PaymentService;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserService;

class ParticipantService extends AbstractService
{
    public function __construct(
        private ParticipantRepository $participantRepository,
        private PaymentService $paymentService,
        private UserRepository $userRepository,
        private UserService $userService,
        private PhpMailerWrapper $mailer,
    ) {}

    /**
     * @param Participant $participant
     * @param string[] $params
     * @return Participant
     */
    public function addParamsIntoParticipant(Participant $participant, array $params): Participant
    {
        $this->addParamsIntoPerson($params, $participant);
        $this->participantRepository->persist($participant);

        return $participant;
    }
    
    public function isCloseRegistrationValid(Participant $participant): bool
    {
        $user = $participant->getUserButNotNull();
        $ca = match ($user->role) {
            User::ROLE_TROOP_LEADER => $user->event->eventType->getContentArbiterTroopLeader(),
            User::ROLE_TROOP_PARTICIPANT => $user->event->eventType->getContentArbiterTroopParticipant(),
            default => throw new \RuntimeException('Unexpected role ' . $user->role),
        };

        return $this->isPersonValidForClose($participant, $ca);
    }

    public function closeRegistration(Participant $participant): Participant
    {
        if ($this->isCloseRegistrationValid($participant)) {
            $user = $participant->getUserButNotNull();
            $this->userService->closeRegistration($user);
            $this->mailer->sendRegistrationClosed($user);
        }

        return $participant;
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
