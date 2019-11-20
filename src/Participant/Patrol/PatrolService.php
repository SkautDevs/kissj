<?php

namespace kissj\Participant\Patrol;

use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Mailer\PhpMailerWrapper;
use kissj\Orm\Relation;
use kissj\Payment\Payment;
use kissj\Payment\PaymentRepository;
use kissj\User\User;
use kissj\User\UserService;

class PatrolService {
    private $patrolLeaderRepository;
    private $patrolParticipantRepository;
    private $paymentRepository;
    private $flashMessages;
    private $mailer;
    private $userService;

    public function __construct(
        PatrolLeaderRepository $patrolLeaderRepository,
        PatrolParticipantRepository $patrolParticipantRepository,
        PaymentRepository $paymentRepository,
        FlashMessagesBySession $flashMessages,
        PhpMailerWrapper $mailer,
        UserService $userService
    ) {
        $this->patrolLeaderRepository = $patrolLeaderRepository;
        $this->patrolParticipantRepository = $patrolParticipantRepository;
        $this->paymentRepository = $paymentRepository;
        $this->flashMessages = $flashMessages;
        $this->mailer = $mailer;
        $this->userService = $userService;
    }

    public function getPatrolLeader(User $user): PatrolLeader {
        if ($this->patrolLeaderRepository->countBy(['user' => $user]) === 0) {
            $patrolLeader = new PatrolLeader();
            $patrolLeader->user = $user;
            $this->patrolLeaderRepository->persist($patrolLeader);
        }

        return $this->patrolLeaderRepository->findOneBy(['user' => $user]);
    }

    public function addParamsIntoPatrolLeader(PatrolLeader $pl, array $params): PatrolLeader {
        $pl->firstName = $params['firstName'] ?? null;
        $pl->lastName = $params['lastName'] ?? null;
        $pl->nickname = $params['nickname'] ?? null;
        if ($params['birthDate'] !== null) {
            $pl->birthDate = new \DateTime($params['birthDate']);
        }
        $pl->gender = $params['gender'] ?? null;
        $pl->email = $params['email'] ?? null;
        $pl->telephoneNumber = $params['telephoneNumber'] ?? null;
        $pl->permanentResidence = $params['permanentResidence'] ?? null;
        $pl->country = $params['country'] ?? null;
        $pl->scoutUnit = $params['scoutUnit'] ?? null;
        /* $pl->setTshirt($params['tshirtShape'] ?? null, $params['tshirtSize'] ?? null); */
        $pl->foodPreferences = $params['foodPreferences'] ?? null;
        $pl->healthProblems = $params['healthProblems'] ?? null;
        $pl->languages = $params['languages'] ?? null;
        $pl->swimming = $params['swimming'] ?? null;
        $pl->patrolName = $params['patrolName'] ?? null;
        $pl->notes = $params['notes'] ?? null;

        return $pl;
    }

    public function isPatrolLeaderValidForClose(PatrolLeader $pl): bool {
        if (
            $pl->patrolName === null
            || $pl->firstName === null
            || $pl->lastName === null
            || $pl->birthDate === null
            || $pl->gender === null
            || $pl->email === null
            || $pl->telephoneNumber === null
            || $pl->permanentResidence === null
            || $pl->country === null
            || $pl->scoutUnit === null
            || $pl->foodPreferences === null
            || $pl->languages === null
            || $pl->swimming === null
            /*|| $pl->getTshirtShape() === null
            || $pl->getTshirtSize() === null*/
        ) {
            return false;
        }

        if (!empty($pl->email) && filter_var($pl->email, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        return true;
    }

    public function addPatrolParticipant(PatrolLeader $patrolLeader): PatrolParticipant {
        $patrolParticipant = new PatrolParticipant();
        $patrolParticipant->patrolLeader = $patrolLeader;

        $this->patrolParticipantRepository->persist($patrolParticipant);

        return $patrolParticipant;
    }

    public function getPatrolParticipant(int $patrolParticipantId): PatrolParticipant {
        $patrolParticipant = $this->patrolParticipantRepository->findOneBy(['id' => $patrolParticipantId]);

        return $patrolParticipant;
    }

    public function addParamsIntoPatrolParticipant(PatrolParticipant $p, array $params): PatrolParticipant {
        $p->firstName = $params['firstName'] ?? null;
        $p->lastName = $params['lastName'] ?? null;
        $p->nickname = $params['nickname'] ?? null;
        if ($params['birthDate'] !== null) {
            $p->birthDate = new \DateTime($params['birthDate']);
        }
        $p->gender = $params['gender'] ?? null;
        $p->email = $params['email'] ?? null;
        $p->telephoneNumber = $params['telephoneNumber'] ?? null;
        $p->permanentResidence = $params['permanentResidence'] ?? null;
        $p->country = $params['country'] ?? null;
        $p->scoutUnit = $params['scoutUnit'] ?? null;
        $p->foodPreferences = $params['foodPreferences'] ?? null;
        $p->healthProblems = $params['healthProblems'] ?? null;
        $p->swimming = $params['swimming'] ?? null;
        $p->notes = $params['notes'] ?? null;

        return $p;
    }

    public function isPatrolParticipantValidForClose(PatrolParticipant $p): bool {
        if (
            $p->firstName === null
            || $p->lastName === null
            || $p->birthDate === null
            || $p->gender === null
            || $p->email === null
            || $p->telephoneNumber === null
            || $p->permanentResidence === null
            || $p->country === null
            || $p->scoutUnit === null
            || $p->foodPreferences === null
            || $p->swimming === null
        ) {
            return false;
        }

        if (!empty($p->email) && filter_var($p->email, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        return true;
    }

    // TODO add telephone check 
    // check for numbers and plus sight up front only
    /*if ((!empty ($telephoneNumber)) && preg_match('/^\+?\d+$/', $telephoneNumber) === 0) {
        $validFlag = false;
    }*/

    /**
     * @param PatrolParticipant $patrolParticipant
     * @throws \LeanMapper\Exception\InvalidStateException
     */
    public function deletePatrolParticipant(PatrolParticipant $patrolParticipant) {
        $this->patrolParticipantRepository->delete($patrolParticipant);
    }

    public function patrolParticipantBelongsPatrolLeader(
        PatrolParticipant $patrolParticipant,
        PatrolLeader $patrolLeader
    ): bool {
        return $patrolParticipant->patrolLeader->id === $patrolLeader->id;
    }

    public function isCloseRegistrationValid(PatrolLeader $patrolLeader): bool {
        $event = $patrolLeader->user->event;
        if ($this->userService->getClosedPatrolsCount() >= $event->maximalClosedPatrolsCount) {
            $this->flashMessages->warning('Cannot lock the registration - for Patrols we have full registration now. Please wait for limit rise');

            return false;
        }

        $validityFlag = true;
        if (!$this->isPatrolLeaderValidForClose($patrolLeader)) {
            $this->flashMessages->warning('Cannot lock the registration - some of your details are wrong or missing (probably email or some date)');

            $validityFlag = false;
        }

        $participants = $this->patrolParticipantRepository->findBy(['patrol_leader_id' => $patrolLeader->id]);
        $participantsCount = count($participants);
        if ($participantsCount < $event->minimalPatrolParticipantsCount) {
            $this->flashMessages->warning('Cannot lock the registration - too few participants, they are only '
                .$participantsCount.' from '.$event->minimalPatrolParticipantsCount.' needed');

            $validityFlag = false;
        }
        if ($participantsCount > $event->maximalPatrolParticipantsCount) {
            $this->flashMessages->warning('Cannot lock the registration - too many participants - they are '
                .$participantsCount.' and you need '.$event->maximalPatrolParticipantsCount.' maximum');

            $validityFlag = false;
        }
        /** @var PatrolParticipant $participant */
        foreach ($participants as $participant) {
            if (!$this->isPatrolParticipantValidForClose($participant)) {
                $this->flashMessages->warning('Cannot lock the registration - some of the '
                    .$participant->getFullName().' details are wrong or missing (probably email or some date)');

                $validityFlag = false;
            }
        }

        // to show all warnings
        return $validityFlag;
    }

    public function closeRegistration(PatrolLeader $patrolLeader): PatrolLeader {
        if ($this->isCloseRegistrationValid($patrolLeader)) {
            $this->userService->closeRegistration($patrolLeader->user);
            $this->mailer->sendRegistrationSentEmail($patrolLeader->user->email);
        }

        return $patrolLeader;
    }

    // TODO fix
    private function getClosedPatrolsCount(): int {
        return $this->roleRepository->countBy([
            'name' => 'patrol-leader',
            'event' => $this->eventName,
            'status' => new Relation('open', '!='),
        ]);
    }

    public function getAllPatrolsStatistics(): array {
        $patrols['limit'] = $this->eventSettings['maximalClosedPatrolsCount'];

        $patrols['closed'] = $this->roleRepository->countBy([
            'name' => 'patrol-leader',
            'event' => $this->eventName,
            'status' => new Relation('closed', '=='),
        ]);

        $patrols['approved'] = $this->roleRepository->countBy([
            'name' => 'patrol-leader',
            'event' => $this->eventName,
            'status' => new Relation('approved', '=='),
        ]);

        $patrols['paid'] = $this->roleRepository->countBy([
            'name' => 'patrol-leader',
            'event' => $this->eventName,
            'status' => new Relation('paid', '=='),
        ]);

        return $patrols;
    }

    public function sendPaymentByMail(Payment $payment, PatrolLeader $patrolLeader) {
        $message = $this->renderer->fetch('emails/payment-info.twig', [
            'eventName' => 'CEJ 2018',
            'accountNumber' => $payment->accountNumber,
            'price' => $payment->price,
            'currency' => 'Kč',
            'variableSymbol' => $payment->variableSymbol,
            'role' => $payment->role->name,
            'gender' => $patrolLeader->gender,

            'patrolName' => $patrolLeader->patrolName,
        ]);

        $this->mailer->sendMailFromTemplate($payment->role->user->email, 'Registrace CEJ 2018 - platební informace',
            $message);
    }

    public function sendDenialMail(PatrolLeader $patrolLeader, string $reason) {
        $message = $this->renderer->fetch('emails/denial.twig', [
            'eventName' => 'CEJ 2018',
            'role' => 'patrol-leader',
            'reason' => $reason,
        ]);

        $this->mailer->sendMailFromTemplate($patrolLeader->user->email, 'Registrace CEJ 2018 - zamítnutí registrace',
            $message);
    }

    // TODO make this more clever
    public function getOnePayment(PatrolLeader $patrolLeader): ?Payment {
        if ($this->paymentRepository->isExisting([
            'roleId' => $this->roleRepository->findOneBy([
                'userId' => $patrolLeader->user->id,
                'event' => 'cej2018',
            ]),
        ])) {
            return $this->paymentRepository->findOneBy([
                'roleId' => $this->roleRepository->findOneBy([
                    'userId' => $patrolLeader->user->id,
                    'event' => 'cej2018',
                ]),
            ]);
        } else {
            return null;
        }
    }

    public function approvePatrol(PatrolLeader $patrolLeader) {
        /** @var Role $role */
        $role = $this->roleRepository->findOneBy(['userId' => $patrolLeader->user->id]);
        $role->status = $this->roleService->getApproveStatus();
        $this->roleRepository->persist($role);
    }

    public function openPatrol(PatrolLeader $patrolLeader) {
        /** @var Role $role */
        $role = $this->roleRepository->findOneBy(['userId' => $patrolLeader->user->id]);
        $role->status = $this->roleService->getOpenStatus();
        $this->roleRepository->persist($role);
    }
}
