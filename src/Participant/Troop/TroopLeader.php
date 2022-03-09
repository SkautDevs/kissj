<?php declare(strict_types=1);

namespace kissj\Participant\Troop;

use kissj\Participant\Participant;

/**
 * @property string|null $troopName
 * @property TroopParticipant[] $troopParticipants m:belongsToMany(troop_leader_id:participant)
 */
class TroopLeader extends Participant
{
}
