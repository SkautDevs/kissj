<?php declare(strict_types=1);

namespace kissj\Participant\Troop;

use kissj\AbstractService;
use kissj\User\User;

class TroopService extends AbstractService
{
    public function __construct(
        private TroopLeaderRepository $troopLeaderRepository,
        private TroopParticipantRepository $troopParticipantRepository,
    ) {
    }
    
    public function getTroopLeader(User $user): TroopLeader
    {
        $troopLeader = $this->troopLeaderRepository->findOneBy(['user' => $user]);

        if ($troopLeader === null) {
            $troopLeader = new TroopLeader();
            $troopLeader->user = $user;
            $this->troopLeaderRepository->persist($troopLeader);
        }

        return $troopLeader;
    }
    
    public function getTroopParticipant(User $user): TroopParticipant
    {
        $troopParticipant = $this->troopParticipantRepository->findOneBy(['user' => $user]);

        if ($troopParticipant === null) {
            $troopParticipant = new TroopParticipant();
            $troopParticipant->user = $user;
            $this->troopParticipantRepository->persist($troopParticipant);
        }

        return $troopParticipant;
    }
}
