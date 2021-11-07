<?php

namespace kissj\Participant\Guest;

use kissj\AbstractService;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Mailer\PhpMailerWrapper;
use kissj\Participant\Admin\StatisticValueObject;
use kissj\User\User;
use kissj\User\UserService;
use Symfony\Contracts\Translation\TranslatorInterface;

class GuestService extends AbstractService
{
    public function __construct(
        private GuestRepository $guestRepository,
        private FlashMessagesBySession $flashMessages,
        private TranslatorInterface $translator,
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

    public function addParamsIntoGuest(Guest $guest, array $params): Guest
    {
        /** @var Guest $guest */
        $guest = $this->addParamsIntoPerson($params, $guest);

        return $guest;
    }

    public function isGuestValidForClose(Guest $guest): bool
    {
        return $this->isPersonValidForClose($guest, $guest->user->event->eventType->getContentArbiterGuest());
    }

    public function isCloseRegistrationValid(Guest $guest): bool
    {
        $validityFlag = true;

        if (!$this->isGuestValidForClose($guest)) {
            $this->flashMessages->warning('flash.warning.guestWrongData');

            $validityFlag = false;
        }

        $event = $guest->user->event;
        if ($this->userService->getClosedIstsCount($event) >= $event->maximalClosedGuestsCount) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.guestFullRegistration'));

            $validityFlag = false;
        }

        return $validityFlag;
    }

    public function closeRegistration(Guest $guest): Guest
    {
        if ($this->isCloseRegistrationValid($guest)) {
            $this->userService->closeRegistration($guest->user);
            $this->mailer->sendRegistrationClosed($guest->user);
        }

        return $guest;
    }

    public function getAllGuestsStatistics(): StatisticValueObject
    {
        $ists = $this->guestRepository->findAll();

        return new StatisticValueObject($ists);
    }

    public function finishRegistration(Guest $guest): Guest
    {
        $this->userService->payRegistration($guest->user);
        $this->mailer->sendGuestRegistrationFinished($guest);

        return $guest;
    }
}
