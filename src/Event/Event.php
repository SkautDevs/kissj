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
 * @property string|null $istLabel
 *
 * @property string|null $eventStart m:passThru(dateFromString|dateToString)
 * @property string|null $contactEmail
 */
class Event extends EntityDatetime {
    public $maximalClosedGuestsCount = 100; // TODO move into DB
    public $maximalClosedPatrolsSlovakCount = 18;
    public $maximalClosedPatrolsCzechCount = 5;
    public $maximalClosedPatrolsOthersCount = 5;
    public $maximalClosedFreeParticipantsCount = 100; // TODO move into DB
}
