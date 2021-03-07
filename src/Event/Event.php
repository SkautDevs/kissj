<?php
declare(strict_types=1);

namespace kissj\Event;

use kissj\Orm\EntityDatetime;

/**
 * @property int         $id
 * @property string      $slug
 * @property string      $readableName
 * @property string      $webUrl
 * @property string      $dataProtectionUrl
 * @property string      $contactEmail
 *
 * @property string      $accountNumber
 * @property int         $prefixVariableSymbol
 * @property bool        $automaticPaymentPairing
 * @property int|null    $bankId
 * @property string|null $bankApiKey
 * @property int         $maxElapsedPaymentDays
 * @property string      $currency
 *
 * @property bool        $allowPatrols
 * @property int|null    $maximalClosedPatrolsCount
 * @property int|null    $minimalPatrolParticipantsCount
 * @property int|null    $maximalPatrolParticipantsCount
 *
 * @property bool        $allowIsts
 * @property int|null    $maximalClosedIstsCount
 *
 * @property bool        $allowGuests
 * @property int|null    $maximalClosedGuestsCount
 *
 * @property string|null $startDay m:passThru(dateFromString|dateToString)
 * @property string|null $endDay m:passThru(dateFromString|dateToString)
 */
class Event extends EntityDatetime {
}
