<?php

namespace kissj\Participant\Ist;

use kissj\Participant\Participant;

/**
 * @table participant
 *
 * @property string|null $arrivalDate  m:passThru(dateFromString|dateToString)
 * @property string|null $departueDate m:passThru(dateFromString|dateToString)
 */
class Ist extends Participant {

}
