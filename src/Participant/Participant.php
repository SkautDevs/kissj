<?php

namespace kissj\Participant;

use DateTimeInterface;
use kissj\Event\EventType\Cej\EventTypeCej;
use kissj\Orm\EntityDatetime;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Payment\Payment;
use kissj\User\User;

/**
 * Master table for all participants, using Single Table Inheritance
 * All commons are here, entitis are seaprated of course (:
 *
 * @property int                    $id
 * @property User|null              $user m:hasOne
 * @property string|null            $role needed for DB working (see Mapper.php) // TODO make definite list of participant roles
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
 * @property string|null            $notes
 *
 * @property Payment[]              $payment m:belongsToMany
 */
class Participant extends EntityDatetime
{
    protected ?string $tshirtSize = null;
    protected ?string $tshirtShape = null;

    protected const TSHIRT_DELIMITER = '-';

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
                throw new \Exception('Missing user for patrol leader ID ' . $this->patrolLeader->id);
            }

            return $patrolLeaderUser;
        }

        if ($this->user === null) {
            throw new \Exception('Missing user for participant ID ' . $this->id);
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

    public function isInSpecialPaymentContingent(): bool
    {
        return $this->contingent !== null
            && $this->contingent !== ''
            && $this->contingent !== EventTypeCej::CONTINGENT_CZECHIA
            && in_array($this->contingent, $this->getUserButNotNull()->event->getEventType()->getContingents(), true);
    }
}
