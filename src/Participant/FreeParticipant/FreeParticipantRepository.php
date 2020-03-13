<?php

namespace kissj\Participant\FreeParticipant;

use kissj\Orm\Repository;

/**
 * @table participant
 */
class FreeParticipantRepository extends Repository {
    /**
     * @return FreeParticipant[]
     */
    public function findAll(): array {
        $FreeParticipantsOnly = [];
        foreach (parent::findAll() as $participant) {
            if ($participant instanceof FreeParticipant) {
                $FreeParticipantsOnly[] = $participant;
            }
        }

        return $FreeParticipantsOnly;
    }
}
