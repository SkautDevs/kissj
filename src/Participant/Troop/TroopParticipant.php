<?php declare(strict_types=1);

namespace kissj\Participant\Troop;

use kissj\Participant\Participant;

/**
 * @property TroopLeader|null $troopLeader m:hasOne(troop_leader_id)
 */
class TroopParticipant extends Participant
{
}
