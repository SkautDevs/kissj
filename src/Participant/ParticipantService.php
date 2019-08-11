<?php

namespace kissj\Participant;

use kissj\Participant\Guest\GuestRepository;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipantRepository;
use kissj\User\User;
use PHPUnit\Framework\MockObject\RuntimeException;

class ParticipantService {
    /** @var ParticipantRepository */
    private $participantRepository;
    /** @var IstRepository */
    private $istRepository;
    /** @var GuestRepository */
    private $guestRepository;
    /** @var PatrolParticipantRepository */
    private $patrolParticipantRepository;
    /** @var PatrolLeaderRepository */
    private $patrolLeaderRepository;

    public function __construct(
        ParticipantRepository $participantRepository,
        IstRepository $istRepository,
        GuestRepository $guestRepository,
        PatrolLeaderRepository $patrolLeaderRepository,
        PatrolParticipantRepository $patrolParticipantRepository
    ) {
        $this->participantRepository = $participantRepository;
        $this->istRepository = $istRepository;
        $this->guestRepository = $guestRepository;
        $this->patrolLeaderRepository = $patrolLeaderRepository;
        $this->patrolParticipantRepository = $patrolParticipantRepository;
    }

    public function getUserRole(User $user): ?string {
        if ($user->status === User::STATUS_WITHOUT_ROLE) {
            return null;
        }

        $participant = $this->participantRepository->findOneBy(['user' => $user]);

        $dbCriteirium = ['participant' => $participant];
        if ($this->istRepository->findOneBy($dbCriteirium)) {
            return User::ROLE_IST;
        }
        if ($this->patrolLeaderRepository->findOneBy($dbCriteirium)) {
            return User::ROLE_PATROL_LEADER;
        }
        if ($this->guestRepository->findOneBy($dbCriteirium)) {
            return User::ROLE_GUEST;
        }

        throw new RuntimeException('User ID '.$user->id.'has status '.$user->status, ', but no role');
    }
}
