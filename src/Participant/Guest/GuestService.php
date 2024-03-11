<?php

declare(strict_types=1);

namespace kissj\Participant\Guest;

use kissj\Mailer\Mailer;
use kissj\User\UserService;

class GuestService
{
    public function __construct(
        private readonly Mailer $mailer,
        private readonly UserService $userService,
    ) {
    }

    public function finishRegistration(Guest $guest): Guest
    {
        $this->userService->setUserPaid($guest->getUserButNotNull());
        $this->mailer->sendGuestRegistrationFinished($guest);

        return $guest;
    }
}
