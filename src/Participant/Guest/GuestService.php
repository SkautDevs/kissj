<?php

declare(strict_types=1);

namespace kissj\Participant\Guest;

use kissj\Event\Event;
use kissj\Mailer\PhpMailerWrapper;
use kissj\Participant\Admin\StatisticValueObject;
use kissj\Participant\ParticipantRepository;
use kissj\User\User;
use kissj\User\UserService;
use kissj\User\UserStatus;

class GuestService
{
    public function __construct(
        private GuestRepository $guestRepository,
        private ParticipantRepository $participantRepository,
        private PhpMailerWrapper $mailer,
        private UserService $userService,
    ) {
    }

    public function getGuest(User $user): Guest
    {
        if ($this->guestRepository->countBy(['user' => $user]) === 0) {
            $guest = new Guest();
            $guest->user = $user;
            $this->guestRepository->persist($guest);
        }

        /** @var Guest $guest */
        $guest = $this->guestRepository->findOneBy(['user' => $user]);

        return $guest;
    }

    public function getAllGuestsStatistics(Event $event, User $admin): StatisticValueObject
    {
        $guests = $this->participantRepository->getAllParticipantsWithStatus(
            [User::ROLE_GUEST],
            UserStatus::cases(),
            $event,
            $admin,
        );

        return new StatisticValueObject($guests);
    }

    public function finishRegistration(Guest $guest): Guest
    {
        $this->userService->payRegistration($guest->getUserButNotNull());
        $this->mailer->sendGuestRegistrationFinished($guest);

        return $guest;
    }
}
