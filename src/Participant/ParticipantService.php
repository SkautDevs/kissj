<?php

namespace kissj\Participant;

use kissj\Participant\Guest\GuestRepository;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipantRepository;

class ParticipantService {
    private $participantRepository;
    private $istRepository;
    private $guestRepository;
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
    }
}