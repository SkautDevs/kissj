<?php

declare(strict_types=1);

namespace kissj\Participant;

use DateTimeInterface;
use kissj\Event\EventType\Cej\EventTypeCej;
use kissj\Orm\EntityDatetime;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Payment\Payment;
use kissj\Payment\PaymentStatus;
use kissj\User\User;

/**
 * Master table for all participants, using Single Table Inheritance
 * All commons are here, entities are separated of course (:
 *
 * @property int                    $id
 * @property User|null              $user m:hasOne
 * @property string|null            $role needed for DB working (see Mapper.php) // TODO make definite list of participant roles
 * @property string|null            $patrolName
 * @property string|null            $contingent
 * @property string|null            $firstName
 * @property string|null            $lastName
 * @property string|null            $nickname
 * @property string|null            $permanentResidence
 * @property string|null            $telephoneNumber
 * @property string|null            $gender
 * @property string|null            $country
 * @property string|null            $email
 * @property string|null            $scoutUnit
 * @property string|null            $languages
 * @property DateTimeInterface|null $birthDate m:passThru(dateFromString|dateToString)
 * @property string|null            $birthPlace
 * @property string|null            $healthProblems
 * @property string|null            $foodPreferences
 * @property string|null            $idNumber
 * @property string|null            $scarf
 * @property string|null            $swimming
 * @property string|null            $tshirt m:useMethods(getTshirt|setThirt)
 * @property DateTimeInterface|null $arrivalDate m:passThru(dateFromString|dateToString)
 * @property DateTimeInterface|null $departureDate (departue_date) m:passThru(dateFromString|dateToString)
 * @property string|null            $uploadedFilename
 * @property string|null            $uploadedOriginalFilename
 * @property string|null            $uploadedContenttype
 * @property string|null            $skills
 * @property array|null             $preferredPosition m:useMethods(getPreferredPosition|setPreferredPosition)
 * @property string|null            $driversLicense
 * @property string|null            $notes
 * @property DateTimeInterface|null $registrationCloseDate m:passThru(dateFromString|dateToString)
 * @property DateTimeInterface|null $registrationApproveDate m:passThru(dateFromString|dateToString)
 * @property DateTimeInterface|null $registrationPayDate m:passThru(dateFromString|dateToString)
 * @property string                 $adminNote
 *
 * @property Payment[]              $payment m:belongsToMany
 */
class Participant extends EntityDatetime
{
    protected ?string $tshirtSize = null;
    protected ?string $tshirtShape = null;

    protected const TSHIRT_DELIMITER = '-';
    protected const PREFERRED_POSITION_DELIMITER = ' & ';

    public const FOOD_OTHER = 'other';
    public const SCARF_NO = 'no';
    public const SCARF_YES = 'yes';

    public function setUser(User $user): void
    {
        // else  LeanMapper \ Exception \ MemberAccessException
        // Cannot write to read-only property 'user' in entity kissj\Participant\Participant.
        $this->row->user_id = $user->id;
        $this->row->cleanReferencedRowsCache('user', 'user_id');
    }

    public function getUserButNotNull(): User
    {
        if ($this instanceof PatrolParticipant) {
            $patrolLeaderUser = $this->patrolLeader->user;
            if ($patrolLeaderUser === null) {
                throw new \RuntimeException('Missing user for patrol leader ID ' . $this->patrolLeader->id);
            }

            return $patrolLeaderUser;
        }

        if ($this->user === null) {
            throw new \RuntimeException('Missing user for participant ID ' . $this->id);
        }

        return $this->user;
    }

    public function getTshirt(): ?string
    {
        return $this->row->tshirt;
    }

    public function setTshirt(?string $shape, ?string $size): void
    {
        $this->row->tshirt = implode(self::TSHIRT_DELIMITER, [$shape, $size]);
        $this->tshirtSize = $size;
        $this->tshirtShape = $shape;
    }

    public function getTshirtShape(): ?string
    {
        return $this->getTshirtParsed()[0] ?? null;
    }

    public function getTshirtSize(): ?string
    {
        return $this->getTshirtParsed()[1] ?? null;
    }

    /**
     * @return string[]
     */
    protected function getTshirtParsed(): array
    {
        $tshirtFromDb = $this->getTshirt();

        return explode(self::TSHIRT_DELIMITER, $tshirtFromDb ?? '');
    }

    public function getFullName(): string
    {
        return ($this->firstName ?? '') . ' ' . ($this->lastName ?? '') . ($this->nickname ? ' - ' . $this->nickname : '');
    }

    public function getAgeAtStartOfEvent(): ?int
    {
        $birthDate = $this->birthDate;
        if ($birthDate === null) {
            return null;
        }

        return (int)$birthDate->diff($this->getUserButNotNull()->event->startDay)->format('%y');
    }

    /**
     * @return Payment[]
     */
    public function getPayments(): array
    {
        return $this->payment;
    }

    /**
     * @return Payment[]
     */
    public function getNoncanceledPayments(): array
    {
        return array_filter($this->payment, function (Payment $payment): bool {
            return $payment->status !== PaymentStatus::Canceled;
        });
    }

    public function isInSpecialPaymentContingent(): bool
    {
        return $this->contingent !== null
            && $this->contingent !== ''
            && $this->contingent !== EventTypeCej::CONTINGENT_CZECHIA
            && $this->contingent !== EventTypeCej::CONTINGENT_TEAM
            && in_array($this->contingent, $this->getUserButNotNull()->event->getEventType()->getContingents(), true);
    }

    public function getQrParticipantInfoString(): string
    {
        return $this->id . '|'
            . $this->getUserButNotNull()->event->readableName . '|'
            . $this->getFullName() . '|'
            . $this->email;
    }

    /**
     * @return string[]
     */
    protected function getPreferredPosition(): array
    {
        $prefferedPositionFromDb = $this->row->preferred_position;
        if ($prefferedPositionFromDb === null || $prefferedPositionFromDb === '') {
            return [];
        }

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
