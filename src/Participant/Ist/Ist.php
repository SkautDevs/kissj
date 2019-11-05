<?php

namespace kissj\Participant\Ist;

use kissj\Participant\Participant;

/**
 * @property string|null $skills
 * @property string|null $preferredPosition m:useMethods
 * @property string|null $driversLicense
 */
class Ist extends Participant {
    protected const PREFERRED_POSITION_DELIMITER = ' & ';

    protected function getPreferredPosition(): array {
        $prefferedPositionFromDb = $this->row->preferred_position;

        return explode(self::PREFERRED_POSITION_DELIMITER, $prefferedPositionFromDb);
    }

    public function setPreferredPosition(array $positions): void {
        $this->row->preferred_position = implode(self::PREFERRED_POSITION_DELIMITER, $positions);
    }
}
