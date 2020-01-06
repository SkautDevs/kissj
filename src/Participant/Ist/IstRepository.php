<?php

namespace kissj\Participant\Ist;

use kissj\Orm\Repository;

/**
 * @table participant
 */
class IstRepository extends Repository {
    /**
     * @return Ist[]
     */
    public function findAll(): array {
        $istsOnly = [];
        foreach (parent::findAll() as $participant) {
            if ($participant instanceof Ist) {
                $istsOnly[] = $participant;
            }
        }

        return $istsOnly;
    }
}
