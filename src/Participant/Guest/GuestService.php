<?php

declare(strict_types=1);

namespace kissj\Participant\Guest;

use kissj\Mailer\Mailer;
use kissj\User\UserService;

readonly class GuestService
{
    public function __construct(
        private Mailer $mailer,
        private UserService $userService,
    ) {
    }

    public function finishRegistration(Guest $guest): Guest
    {
        $this->userService->setUserPaid($guest->getUserButNotNull());
        $this->mailer->sendGuestRegistrationFinished($guest);

        return $guest;
    }
}
