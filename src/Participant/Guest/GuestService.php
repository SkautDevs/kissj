<?php

declare(strict_types=1);

namespace kissj\Participant\Guest;

use kissj\Event\Event;
use kissj\Mailer\PhpMailerWrapper;
use kissj\Participant\Admin\StatisticValueObject;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\User\User;
use kissj\User\UserService;
use kissj\User\UserStatus;

class GuestService
{
    public function __construct(
        private readonly ParticipantRepository $participantRepository,
        private readonly PhpMailerWrapper $mailer,
        private readonly UserService $userService,
    ) {
    }

    public function getAllGuestsStatistics(Event $event, User $admin): StatisticValueObject
    {
        $guests = $this->participantRepository->getAllParticipantsWithStatus(
            [ParticipantRole::Guest],
            UserStatus::cases(),
            $event,
            $admin,
        );

        return new StatisticValueObject($guests);
    }

    public function finishRegistration(Guest $guest): Guest
    {
        $this->userService->setUserPaid($guest->getUserButNotNull());
        $this->mailer->sendGuestRegistrationFinished($guest);

        return $guest;
    }
}
