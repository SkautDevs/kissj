<?php declare(strict_types=1);

namespace kissj\Participant\Guest;

use kissj\AbstractService;
use kissj\Event\Event;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Mailer\PhpMailerWrapper;
use kissj\Participant\Admin\StatisticValueObject;
use kissj\Participant\ParticipantRepository;
use kissj\User\User;
use kissj\User\UserService;
use Symfony\Contracts\Translation\TranslatorInterface;

class GuestService extends AbstractService
{
    public function __construct(
        private GuestRepository $guestRepository,
        private ParticipantRepository $participantRepository,
        private FlashMessagesBySession $flashMessages,
        private TranslatorInterface $translator,
        private PhpMailerWrapper $mailer,
        private UserService $userService,
    ) {}

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

    /**
     * @param Guest $guest
     * @param string[] $params
     * @return Guest
     */
    public function addParamsIntoGuest(Guest $guest, array $params): Guest
    {
        /** @var Guest $guest */
        $guest = $this->addParamsIntoPerson($params, $guest);

        return $guest;
    }

    public function isGuestValidForClose(Guest $guest): bool
    {
        return $this->isPersonValidForClose(
            $guest,
            $guest->getUserButNotNull()->event->eventType->getContentArbiterGuest(),
        );
    }

    public function isCloseRegistrationValid(Guest $guest): bool
    {
        $validityFlag = true;

        if (!$this->isGuestValidForClose($guest)) {
            $this->flashMessages->warning('flash.warning.guestWrongData');

            $validityFlag = false;
        }

        $event = $guest->getUserButNotNull()->event;
        if (
            $this->userService->getClosedIstsCount($event)
            >= $event->getEventType()->getMaximumClosedParticipants($guest)
        ) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.guestFullRegistration'));

            $validityFlag = false;
        }

        if (!$event->canRegistrationBeLocked()) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.registrationNotAllowed'));

            $validityFlag = false;
        }

        return $validityFlag;
    }

    public function closeRegistration(Guest $guest): Guest
    {
        if ($this->isCloseRegistrationValid($guest)) {
            $user = $guest->getUserButNotNull();
            $this->userService->closeRegistration($user);
            $this->mailer->sendRegistrationClosed($user);
        }

        return $guest;
    }

    public function getAllGuestsStatistics(Event $event, User $admin): StatisticValueObject
    {
        $guests = $this->participantRepository->getAllParticipantsWithStatus(
            [User::ROLE_GUEST],
            User::STATUSES,
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
