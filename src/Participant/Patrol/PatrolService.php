<?php

declare(strict_types=1);

namespace kissj\Participant\Patrol;

use kissj\Application\DateTimeUtils;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Mailer\Mailer;
use kissj\Participant\ParticipantRole;
use kissj\Participant\ParticipantService;
use kissj\User\User;
use kissj\User\UserService;
use Symfony\Contracts\Translation\TranslatorInterface;

class PatrolService
{
    public function __construct(
        private readonly PatrolLeaderRepository $patrolLeaderRepository,
        private readonly PatrolParticipantRepository $patrolParticipantRepository,
        private readonly UserService $userService,
        private readonly ParticipantService $participantService,
        private readonly FlashMessagesBySession $flashMessages,
        private readonly TranslatorInterface $translator,
        private readonly Mailer $mailer,
    ) {
    }

    public function getPatrolLeader(User $user): PatrolLeader
    {
        $patrolLeader = $this->patrolLeaderRepository->findOneBy(['user' => $user]);

        if ($patrolLeader === null) {
            // TODO remove, it is here only for testing
            $patrolLeader = new PatrolLeader();
            $patrolLeader->user = $user;
            $patrolLeader->role = ParticipantRole::PatrolLeader;
            $this->patrolLeaderRepository->persist($patrolLeader);
        }

        return $patrolLeader;
    }

    public function addPatrolParticipant(PatrolLeader $patrolLeader): PatrolParticipant
    {
        $patrolParticipant = new PatrolParticipant();
        $patrolParticipant->patrolLeader = $patrolLeader;

        $this->patrolParticipantRepository->persist($patrolParticipant);

        return $patrolParticipant;
    }

    // TODO refactor to repository->get()
    public function getPatrolParticipant(int $patrolParticipantId): PatrolParticipant
    {
        return $this->patrolParticipantRepository->getOneBy(['id' => $patrolParticipantId]);
    }

    public function deletePatrolParticipant(PatrolParticipant $patrolParticipant): void
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
        $validityFlag = $this->participantService->isCloseRegistrationValid($patrolLeader);
        $event = $patrolLeader->getUserButNotNull()->event;
        $participants = $patrolLeader->patrolParticipants;

        $participantsCount = count($participants);
        if ($participantsCount < $event->getMinimalPpCount($patrolLeader)) {
            $this->flashMessages->warning(
                $this->translator->trans(
                    'flash.warning.plTooFewParticipants',
                    ['%minimalPatrolParticipantsCount%' => $event->getMinimalPpCount($patrolLeader)],
                )
            );

            $validityFlag = false;
        }
        if ($participantsCount > $event->getMaximalPpCount($patrolLeader)) {
            $this->flashMessages->warning(
                $this->translator->trans(
                    'flash.warning.plTooManyParticipants',
                    ['%maximalPatrolParticipantsCount%' => $event->getMaximalPpCount($patrolLeader)],
                )
            );

            $validityFlag = false;
        }

        $contentArbiterPatrolParticipant = $event->getEventType()->getContentArbiterPatrolParticipant();
        foreach ($participants as $participant) {
            if (!$this->participantService->isParticipantDataValidForClose(
                $participant,
                $contentArbiterPatrolParticipant,
            )) {
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

    public function closeRegistration(PatrolLeader $patrolLeader): PatrolLeader
    {
        if ($this->isCloseRegistrationValid($patrolLeader)) {
            $user = $patrolLeader->getUserButNotNull();
            $patrolLeader->registrationCloseDate = DateTimeUtils::getDateTime();
            $this->patrolLeaderRepository->persist($patrolLeader);
            $this->userService->setUserClosed($user);
            $this->mailer->sendRegistrationClosed($user);
        }

        return $patrolLeader;
    }
}
