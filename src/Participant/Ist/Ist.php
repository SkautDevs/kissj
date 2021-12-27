<?php declare(strict_types=1);

namespace kissj\Participant\Ist;

use kissj\Participant\Participant;

/**
 * @property string|null $skills
 * @property array $preferredPosition m:useMethods(getPreferredPosition|setPreferredPosition)
 * @property string|null $driversLicense
 */
class Ist extends Participant
{
    protected const PREFERRED_POSITION_DELIMITER = ' & ';

    /**
     * @return string[]
     */
    protected function getPreferredPosition(): array
    {
        $prefferedPositionFromDb = $this->row->preferred_position;

        return explode(self::PREFERRED_POSITION_DELIMITER, $prefferedPositionFromDb);
    }

    /**
     * @param string[] $positions
     * @return void
     */
    public function setPreferredPosition(array $positions): void
    {
        $this->row->preferred_position = implode(self::PREFERRED_POSITION_DELIMITER, $positions);
    }
}
