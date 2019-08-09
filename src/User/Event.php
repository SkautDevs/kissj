<?php

namespace kissj\Event;

use LeanMapper\Entity;


/**
 * @property int         $id
 * @property string      $slug                          ,
 * @property string      $readableName                  ,
 *
 * @property string      $accountNumber                 ,
 * @property int         $prefixVariableSymbol          ,
 * @property bool        $automaticPaymentPairing       ,
 * @property int         $bankId                        ,
 * @property string|null $bankApi                       ,
 *
 * @property int         $allowPatrols                  ,
 * @property int         $maximalClosedPatrolsCount     ,
 * @property int         $minimalPatrolParticipantsCount,
 * @property int         $maximalPatrolParticipantsCount,
 *
 * @property int         $allowIsts                     ,
 * @property int         $maximalClosedIstsCount        ,
 */
class Event extends Entity {

}
