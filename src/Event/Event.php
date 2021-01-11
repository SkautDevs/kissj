<?php

namespace kissj\Event;

use kissj\Orm\EntityDatetime;

/**
 * @property int         $id
 * @property string      $slug
 * @property string      $readableName
 * @property string      $webUrl
 * @property string      $dataProtectionUrl
 *
 * @property string      $accountNumber
 * @property int         $prefixVariableSymbol
 * @property bool        $automaticPaymentPairing
 * @property int|null    $bankId
 * @property string|null $bankApiKey
 * @property int         $maxElapsedPaymentDays
 * @property int|null    $scarfPrice
 * @property int|null    $thirtPrice
 * @property int|null    $dietPrice
 *
 * @property bool        $allowPatrols
 * @property int|null    $maximalClosedPatrolsCount
 * @property int|null    $minimalPatrolParticipantsCount
 * @property int|null    $maximalPatrolParticipantsCount
 *
 * @property bool        $allowIsts
 * @property int|null    $maximalClosedIstsCount
 * @property string|null $istLabel // TODO remove in favor of translation
 *
 * @property string|null $eventStart m:passThru(dateFromString|dateToString) // TODO rename to startDay
 * @property string|null $contactEmail
 */
class Event extends EntityDatetime {
    public int $maximalClosedGuestsCount = 100; // TODO move into DB
    public int $maximalClosedPatrolsSlovakCount = 18;
    public int $maximalClosedPatrolsCzechCount = 5;
    public int $maximalClosedPatrolsOthersCount = 5;
    public int $maximalClosedFreeParticipantsCount = 100; // TODO move into DB
    public \DateTime $finishDay; // TODO move into DB
}
