<?php

declare(strict_types=1);

namespace kissj\Participant;

use DateTimeInterface;
use kissj\Orm\EntityDatetime;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Deal\Deal;
use kissj\Payment\Payment;
use kissj\Payment\PaymentStatus;
use kissj\User\User;
use Ramsey\Uuid\Uuid;

/**
 * Master table for all participants, using Single Table Inheritance
 * All commons are here, entities are separated of course (:
 *
 * @property int                    $id
 * @property User|null              $user m:hasOne
 * @property ParticipantRole|null   $role m:passThru(roleFromString|roleToString) #needed for DB working (see Mapper.php)
 * @property string|null            $patrolName #used for troops too # TODO move to PatrolLeader + TroopLeader
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
 * @property string|null            $medicaments
 * @property string|null            $psychicalHealthProblems
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
 * @property boolean|null           $printedHandbook
 * @property string|null            $notes
 * @property DateTimeInterface|null $registrationCloseDate m:passThru(dateFromString|dateToString)
 * @property DateTimeInterface|null $registrationApproveDate m:passThru(dateFromString|dateToString)
 * @property DateTimeInterface|null $registrationPayDate m:passThru(dateFromString|dateToString)
 * @property string                 $adminNote
 * @property string                 $tieCode
 * @property string                 $entryCode
 * @property DateTimeInterface|null $entryDate m:passThru(dateFromString|dateToString)
 * @property DateTimeInterface|null $leaveDate m:passThru(dateFromString|dateToString)
 *
 * @property Payment[]              $payment m:belongsToMany
 * @property Deal[]      $deals m:belongsToMany
 */
class Participant extends EntityDatetime
{
    protected ?string $tshirtSize = null;
    protected ?string $tshirtShape = null;

    protected const int TIE_CODE_LENGTH = 6;
    protected const string TSHIRT_DELIMITER = '-';
    protected const string PREFERRED_POSITION_DELIMITER = ' & ';

    public const string FOOD_OTHER = 'other';
    public const string SCARF_NO = 'no';
    public const string SCARF_YES = 'yes';

    protected function initDefaults(): void
    {
        parent::initDefaults();
        $this->tieCode = $this->generateTieCode(); // TODO check if another code exists in DB
        $this->entryCode = Uuid::uuid4()->toString();
        $this->adminNote = '';
    }

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
        return ($this->firstName ?? '')
            . ' ' . ($this->lastName ?? '')
            . (($this->nickname !== null && $this->nickname !== '') ? ' - ' . $this->nickname : '');
    }

    public function isFullNameNotEmpty(): bool
    {
        return $this->getFullName() !== ' ';
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

    public function getFirstPaidPayment(): ?Payment
    {
        return array_filter(
            $this->getPayments(),
            fn (Payment $payment): bool => $payment->status === PaymentStatus::Paid,
        )[0] ?? null;
    }

    /**
     * @return Payment[]
     */
    public function getNoncanceledPayments(): array
    {
        // TODO optimalize
        return array_filter($this->payment, function (Payment $payment): bool {
            return $payment->status !== PaymentStatus::Canceled;
        });
    }

    public function getQrParticipantInfoString(): string
    {
        return $this->entryCode;
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

        return explode(self::PREFERRED_POSITION_DELIMITER, (string)$prefferedPositionFromDb);
    }

    /**
     * @param string[] $positions
     */
    public function setPreferredPosition(array $positions): void
    {
        $this->row->preferred_position = implode(self::PREFERRED_POSITION_DELIMITER, $positions);
    }

    public function roleFromString(string $role): ParticipantRole
    {
        return ParticipantRole::from($role);
    }

    public function roleToString(ParticipantRole $role): string
    {
        return $role->value;
    }

    private function generateTieCode(): string
    {
        return substr(str_shuffle(str_repeat('ABCDEFGHJKLMNOPQRSTUVWXYZ', self::TIE_CODE_LENGTH * 9)), 0, self::TIE_CODE_LENGTH);
    }

    public function findDeal(string $slug): ?Deal
    {
        foreach ($this->deals as $deal) {
            if ($deal->slug === $slug) {
                return $deal;
            }
        }

        return null;
    }
}
