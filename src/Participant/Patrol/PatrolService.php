<?php

namespace kissj\Participant\Patrol;

use kissj\AbstractService;
use kissj\Event\ContentArbiterPatrolLeader;
use kissj\Event\ContentArbiterPatrolParticipant;
use kissj\Event\Event;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Mailer\PhpMailerWrapper;
use kissj\Participant\Admin\StatisticValueObject;
use kissj\Participant\ParticipantRepository;
use kissj\Payment\PaymentService;
use kissj\User\User;
use kissj\User\UserService;
use Symfony\Contracts\Translation\TranslatorInterface;

class PatrolService extends AbstractService
{
    public function __construct(
        private PatrolLeaderRepository $patrolLeaderRepository,
        private PatrolParticipantRepository $patrolParticipantRepository,
        private UserService $userService,
        private PaymentService $paymentService,
        private ParticipantRepository $participantRepository,
        private FlashMessagesBySession $flashMessages,
        private TranslatorInterface $translator,
        private PhpMailerWrapper $mailer,
        private ContentArbiterPatrolLeader $contentArbiterPatrolLeader,
        private ContentArbiterPatrolParticipant $contentArbiterPatrolParticipant,
    ) {
    }

    public function getPatrolLeader(User $user): PatrolLeader
    {
        if ($this->patrolLeaderRepository->countBy(['user' => $user]) === 0) {
            $patrolLeader = new PatrolLeader();
            $patrolLeader->user = $user;
            $this->patrolLeaderRepository->persist($patrolLeader);
        }

        return $this->patrolLeaderRepository->findOneBy(['user' => $user]);
    }

    public function addParamsIntoPatrolLeader(PatrolLeader $pl, array $params): PatrolLeader
    {
        $this->addParamsIntoPerson($params, $pl);
        $pl->patrolName = $params['patrolName'] ?? null;

        return $pl;
    }

    public function addPatrolParticipant(PatrolLeader $patrolLeader): PatrolParticipant
    {
        $patrolParticipant = new PatrolParticipant();
        $patrolParticipant->patrolLeader = $patrolLeader;

        $this->patrolParticipantRepository->persist($patrolParticipant);

        return $patrolParticipant;
    }

    public function getPatrolParticipant(int $patrolParticipantId): PatrolParticipant
    {
        return $this->patrolParticipantRepository->findOneBy(['id' => $patrolParticipantId]);
    }

    public function addParamsIntoPatrolParticipant(PatrolParticipant $participant, array $params): PatrolParticipant
    {
        $this->addParamsIntoPerson($params, $participant);

        return $participant;
    }

    public function deletePatrolParticipant(PatrolParticipant $patrolParticipant)
    {
        $this->patrolParticipantRepository->delete($patrolParticipant);
    }

    public function patrolParticipantBelongsPatrolLeader(
        PatrolParticipant $patrolParticipant,
        PatrolLeader $patrolLeader
    ): bool {
        return $patrolParticipant->patrolLeader->id === $patrolLeader->id;
    }

    public function isCloseRegistrationValid(PatrolLeader $patrolLeader): bool
    {
        $validityFlag = true;

        $event = $patrolLeader->user->event;
        if (
            $this->userService->getClosedPatrolsCount($event)
            >= $event->getEventType()->getMaximumClosedParticipants($patrolLeader)
        ) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.plFullRegistration'));

            $validityFlag = false;
        }

        if (!$event->canRegistrationBeLocked()) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.registrationNotAllowed'));

            $validityFlag = false;
        }

        if (!$this->isPatrolLeaderValidForClose($patrolLeader)) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.plWrongData'));

            $validityFlag = false;
        }

        $participants = $this->patrolParticipantRepository->findBy(['patrol_leader_id' => $patrolLeader->id]);
        $participantsCount = count($participants);
        if ($participantsCount < $event->minimalPatrolParticipantsCount) {
            $this->flashMessages->warning(
                $this->translator->trans(
                    'flash.warning.plTooFewParticipants',
                    ['%minimalPatrolParticipantsCount%' => $event->minimalPatrolParticipantsCount],
                )
            );

            $validityFlag = false;
        }
        if ($participantsCount > $event->maximalPatrolParticipantsCount) {
            $this->flashMessages->warning(
                $this->translator->trans(
                    'flash.warning.plTooManyParticipants',
                    ['%maximalPatrolParticipantsCount%' => $event->maximalPatrolParticipantsCount],
                )
            );

            $validityFlag = false;
        }
        /** @var PatrolParticipant $participant */
        foreach ($participants as $participant) {
            if (!$this->isPatrolParticipantValidForClose($participant)) {
                $this->flashMessages->warning(
                    $this->translator->trans(
                        'flash.warning.plWrongDataParticipant',
                        ['%participantFullName%' => $participant->getFullName()],
                    )
                );

                $validityFlag = false;
            }
        }

        // to show all warnings
        return $validityFlag;
    }

    private function isPatrolLeaderValidForClose(PatrolLeader $pl): bool
    {
        if ($this->contentArbiterPatrolLeader->patrolName && $pl->patrolName === null) {
            return false;
        }

        return $this->isPersonValidForClose($pl, $this->contentArbiterPatrolLeader);
    }

    private function isPatrolParticipantValidForClose(PatrolParticipant $p): bool
    {
        return $this->isPersonValidForClose($p, $this->contentArbiterPatrolParticipant);
    }

    public function closeRegistration(PatrolLeader $patrolLeader): PatrolLeader
    {
        if ($this->isCloseRegistrationValid($patrolLeader)) {
            $this->userService->closeRegistration($patrolLeader->user);
            $this->mailer->sendRegistrationClosed($patrolLeader->user);
        }

        return $patrolLeader;
    }

    public function approveRegistration(PatrolLeader $patrolLeader): PatrolLeader
    {
        $payment = $this->paymentService->createAndPersistNewPayment($patrolLeader);

        $this->mailer->sendRegistrationApprovedWithPayment($patrolLeader, $payment);
        $this->userService->approveRegistration($patrolLeader->user);

        return $patrolLeader;
    }

    public function getAllPatrolsStatistics(Event $event, User $admin): StatisticValueObject
    {
        $patrolLeaders = $this->participantRepository->getAllParticipantsWithStatus(
            [User::ROLE_PATROL_LEADER],
            User::STATUSES,
            $event,
            $admin,
        );

        return new StatisticValueObject($patrolLeaders);
    }
}
