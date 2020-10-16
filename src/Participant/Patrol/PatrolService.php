<?php

namespace kissj\Participant\Patrol;

use kissj\AbstractService;
use kissj\Event\ContentArbiterPatrolLeader;
use kissj\Event\ContentArbiterPatrolParticipant;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Mailer\PhpMailerWrapper;
use kissj\Participant\Admin\StatisticValueObject;
use kissj\Payment\PaymentService;
use kissj\User\User;
use kissj\User\UserService;
use Symfony\Contracts\Translation\TranslatorInterface;

class PatrolService extends AbstractService {
    private PatrolLeaderRepository $patrolLeaderRepository;
    private PatrolParticipantRepository $patrolParticipantRepository;
    private UserService $userService;
    private PaymentService $paymentService;
    private PhpMailerWrapper $mailer;
    private TranslatorInterface $translator;
    private FlashMessagesBySession $flashMessages;
    private ContentArbiterPatrolLeader $contentArbiterPatrolLeader;
    private ContentArbiterPatrolParticipant $contentArbiterPatrolParticipant;

    public function __construct(
        PatrolLeaderRepository $patrolLeaderRepository,
        PatrolParticipantRepository $patrolParticipantRepository,
        UserService $userService,
        PaymentService $paymentService,
        FlashMessagesBySession $flashMessages,
        TranslatorInterface $translator,
        PhpMailerWrapper $mailer,
        ContentArbiterPatrolLeader $contentArbiterPatrolLeader,
        ContentArbiterPatrolParticipant $contentArbiterPatrolParticipant
    ) {
        $this->patrolLeaderRepository = $patrolLeaderRepository;
        $this->patrolParticipantRepository = $patrolParticipantRepository;
        $this->userService = $userService;
        $this->paymentService = $paymentService;
        $this->flashMessages = $flashMessages;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->contentArbiterPatrolLeader = $contentArbiterPatrolLeader;
        $this->contentArbiterPatrolParticipant = $contentArbiterPatrolParticipant;
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
        $this->addParamsIntoPerson($params, $pl);
        $pl->patrolName = $params['patrolName'] ?? null;

        return $pl;
    }

    public function addPatrolParticipant(PatrolLeader $patrolLeader): PatrolParticipant {
        $patrolParticipant = new PatrolParticipant();
        $patrolParticipant->patrolLeader = $patrolLeader;

        $this->patrolParticipantRepository->persist($patrolParticipant);

        return $patrolParticipant;
    }

    public function getPatrolParticipant(int $patrolParticipantId): PatrolParticipant {
        return $this->patrolParticipantRepository->findOneBy(['id' => $patrolParticipantId]);
    }

    public function addParamsIntoPatrolParticipant(PatrolParticipant $participant, array $params): PatrolParticipant {
        $this->addParamsIntoPerson($params, $participant);

        return $participant;
    }

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
        $validityFlag = true;

        $event = $patrolLeader->user->event;
        if ($event->maximalClosedPatrolsCount <= $this->userService->getClosedPatrolsCount()) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.istNoLock'));

            $validityFlag = false;
        }

        switch ($patrolLeader->country) {
            case 'Slovak':
                $localMaxNumber = $event->maximalClosedPatrolsSlovakCount;
                break;
            case 'Czech':
                $localMaxNumber = $event->maximalClosedPatrolsCzechCount;
                break;
            case 'other':
                $localMaxNumber = $event->maximalClosedPatrolsOthersCount;
                break;
            default:
                $this->flashMessages->warning('Cannot determine your country properly');

                return false;
        }
        if ($localMaxNumber <= $this->userService->getClosedPatrolsCount()) {
            $this->flashMessages->warning('Cannot lock the registration - for Patrols from your country 
                we have full registration now. Please wait for limit rise');

            return false;
        }

        if (!$this->isPatrolLeaderValidForClose($patrolLeader)) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.istNoLock'));

            $validityFlag = false;
        }

        $participants = $this->patrolParticipantRepository->findBy(['patrol_leader_id' => $patrolLeader->id]);
        $participantsCount = count($participants);
        if ($participantsCount < $event->minimalPatrolParticipantsCount) {
            // TODO translate
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

    private function isPatrolLeaderValidForClose(PatrolLeader $pl): bool {
        if ($this->contentArbiterPatrolLeader->patrolName && $pl->patrolName === null) {
            return false;
        }

        return $this->isPersonValidForClose($pl, $this->contentArbiterPatrolLeader);
    }

    private function isPatrolParticipantValidForClose(PatrolParticipant $p): bool {
        return $this->isPersonValidForClose($p, $this->contentArbiterPatrolParticipant);
    }

    public function closeRegistration(PatrolLeader $patrolLeader): PatrolLeader {
        if ($this->isCloseRegistrationValid($patrolLeader)) {
            $this->userService->closeRegistration($patrolLeader->user);
            $this->mailer->sendRegistrationClosed($patrolLeader->user);
        }

        return $patrolLeader;
    }

    public function openRegistration(PatrolLeader $patrolLeader, string $reason): PatrolLeader {
        $this->mailer->sendDeniedRegistration($patrolLeader, $reason);
        $this->userService->openRegistration($patrolLeader->user);

        return $patrolLeader;
    }

    public function approveRegistration(PatrolLeader $patrolLeader): PatrolLeader {
        $payment = $this->paymentService->createAndPersistNewPayment($patrolLeader);

        $this->mailer->sendRegistrationApprovedWithPayment($patrolLeader, $payment);
        $this->userService->approveRegistration($patrolLeader->user);

        return $patrolLeader;
    }

    public function getAllPatrolsStatistics(): StatisticValueObject {
        $patrolLeaders = $this->patrolLeaderRepository->findAll();

        return new StatisticValueObject($patrolLeaders);
    }
}
