<?php declare(strict_types=1);

namespace kissj\Participant\Ist;

use kissj\Event\Event;
use kissj\Participant\Admin\StatisticValueObject;
use kissj\Participant\ParticipantRepository;
use kissj\User\User;

class IstService
{
    public function __construct(
        private IstRepository $istRepository,
        private ParticipantRepository $participantRepository,
    ) {
    }

    public function getIst(User $user): Ist
    {
        if ($this->istRepository->countBy(['user' => $user]) === 0) {
            $ist = new Ist();
            $ist->user = $user;
            $this->istRepository->persist($ist);
        }

        /** @var Ist $ist */
        $ist = $this->istRepository->findOneBy(['user' => $user]);

        return $ist;
    }

    public function getAllIstsStatistics(Event $event, User $admin): StatisticValueObject
    {
        $ists = $this->participantRepository->getAllParticipantsWithStatus(
            [User::ROLE_IST],
            User::STATUSES,
            $event,
            $admin,
        );

        return new StatisticValueObject($ists);
    }
}
