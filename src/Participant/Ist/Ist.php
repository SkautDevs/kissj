<?php

namespace kissj\Participant\Ist;

use kissj\Participant\Participant;

/**
 * @property string|null $skills
 * @property string|null $preferredPosition
 * @property string|null $driversLicense
 * @property string|null $arrivalDate  m:passThru(dateFromString|dateToString)
 * @property string|null $departueDate m:passThru(dateFromString|dateToString)
 */
class Ist extends Participant {

}
