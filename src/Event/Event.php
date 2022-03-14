<?php

declare(strict_types=1);

namespace kissj\Event;

use DateTimeImmutable;
use DateTimeInterface;
use kissj\Event\EventType\EventType;
use kissj\Event\EventType\EventTypeAqua;
use kissj\Event\EventType\Cej\EventTypeCej;
use kissj\Event\EventType\EventTypeDefault;
use kissj\Event\EventType\EventTypeKorbo;
use kissj\Event\EventType\EventTypeMiquik;
use kissj\Event\EventType\Navigamus\EventTypeNavigamus;
use kissj\Event\EventType\EventTypeNsj;
use kissj\Event\EventType\Wsj\EventTypeWsj;
use kissj\Orm\EntityDatetime;

/**
 * @property int               $id
 * @property string            $slug
 * @property string            $readableName
 * @property string            $webUrl
 * @property string            $dataProtectionUrl
 * @property string            $contactEmail
 * @property EventType         $eventType m:useMethods(getEventType|)
 * @property string            $logoUrl
 * @property bool              $testingSite
 *
 * @property string            $accountNumber with bank code after slash
 * @property int               $prefixVariableSymbol // TODO add null
 * @property bool              $automaticPaymentPairing
 * @property int|null          $bankId currently not in use
 * @property string|null       $bankApiKey
 * @property int               $maxElapsedPaymentDays
 * @property int               $defaultPrice
 * @property string            $currency
 *
 * @property bool              $allowPatrols
 * @property int|null          $maximalClosedPatrolsCount
 * @property int|null          $minimalPatrolParticipantsCount
 * @property int|null          $maximalPatrolParticipantsCount
 *
 * @property bool              $allowIsts
 * @property int|null          $maximalClosedIstsCount
 *
 * @property bool              $allowGuests
 * @property int|null          $maximalClosedGuestsCount
 *
 * @property bool              $allowTroops
 * @property int|null          $maximalClosedTroopsCount
 * @property int|null          $minimalTroopParticipantsCount
 * @property int|null          $maximalTroopParticipantsCount
 *
 * @property DateTimeInterface $startRegistration m:passThru(dateFromString|dateToString)
 * @property DateTimeInterface $startDay m:passThru(dateFromString|dateToString)
 * @property DateTimeInterface $endDay m:passThru(dateFromString|dateToString)
 *
 * @property string            $emailFrom
 * @property string            $emailFromName
 * @property string|null       $emailBccFrom
 */
class Event extends EntityDatetime
{
    public function getEventType(): EventType
    {
        $eventTypeClass = match ($this->row->event_type) {
            'default' => EventTypeDefault::class,
            'aqua' => EventTypeAqua::class,
            'cej' => EventTypeCej::class,
            'korbo' => EventTypeKorbo::class,
            'miquik' => EventTypeMiquik::class,
            'navigamus' => EventTypeNavigamus::class,
            'nsj' => EventTypeNsj::class,
            'wsj' => EventTypeWsj::class,
            default => throw new \RuntimeException('unknown event type: ' . $this->row->event_type),
        };

        return new $eventTypeClass;
    }
    
    public function canRegistrationBeLocked(): bool // TODO fix
    {
        return $this->startRegistration <= new DateTimeImmutable('now', new \DateTimeZone('Europe/Berlin'));
    }
    
    public function getLogoInBase64(): string
    {
        try {
            $logo = file_get_contents(__DIR__ . '/../../public' . $this->logoUrl);
        } catch (\Exception $e) {
            $logo = false;
        }

        if ($logo === false) {
            return '';
        }

        return base64_encode($logo);
    }
}
