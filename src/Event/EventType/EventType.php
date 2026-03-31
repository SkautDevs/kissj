<?php

declare(strict_types=1);

namespace kissj\Event\EventType;

use kissj\Application\StringUtils;
use kissj\Event\AbstractContentArbiter;
use kissj\Event\ContentArbiterGuest;
use kissj\Event\ContentArbiterIst;
use kissj\Event\ContentArbiterOrganizingTeam;
use kissj\Event\ContentArbiterPatrolLeader;
use kissj\Event\ContentArbiterPatrolParticipant;
use kissj\Event\ContentArbiterTroopLeader;
use kissj\Event\ContentArbiterTroopParticipant;
use kissj\Event\Event;
use kissj\Participant\Guest\Guest;
use kissj\Participant\OrganizingTeam\OrganizingTeam;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRole;
use kissj\Deal\EventDeal;
use kissj\Payment\Payment;
use kissj\User\UserRole;
use kissj\User\UserStatus;

abstract class EventType
{
    public const string SLUG_SFH = 'sfh';

    public function transformPaymentPrice(Payment $payment, Participant $participant): Payment
    {
        $payment->price = (string)$this->getPrice($participant);

        return $payment;
    }

    public function getPrice(Participant $participant): int
    {
        if ($participant instanceof Guest) {
            return $participant->getUserButNotNull()->event->guestPrice ?? 0;
        }

        if ($participant instanceof OrganizingTeam) {
            return $participant->getUserButNotNull()->event->organizingTeamPrice ?? 0;
        }

        return $participant->getUserButNotNull()->event->defaultPrice;
    }

    public function isFullForParticipant(
        Participant $participant,
        int $closedSameRoleSameContingentParticipantsCount,
    ): bool {
        if ($participant->role === null) {
            throw new \LogicException('Unexpected participant without role, ID: ' . $participant->id);
        }

        $event = $participant->getUserButNotNull()->event;
        $maximumParticipants = $this->getMaximalCountForRole($event, $participant->role);

        return $maximumParticipants <= $closedSameRoleSameContingentParticipantsCount;
    }

    public function getContentArbiterPatrolLeader(): ContentArbiterPatrolLeader
    {
        return new ContentArbiterPatrolLeader();
    }

    public function getContentArbiterPatrolParticipant(): ContentArbiterPatrolParticipant
    {
        return new ContentArbiterPatrolParticipant();
    }

    public function getContentArbiterIst(): ContentArbiterIst
    {
        return new ContentArbiterIst();
    }

    public function getContentArbiterGuest(): ContentArbiterGuest
    {
        return new ContentArbiterGuest();
    }

    public function getContentArbiterTroopLeader(): ContentArbiterTroopLeader
    {
        return new ContentArbiterTroopLeader();
    }

    public function getContentArbiterTroopParticipant(): ContentArbiterTroopParticipant
    {
        return new ContentArbiterTroopParticipant();
    }

    public function getContentArbiterOrganizingTeam(): ContentArbiterOrganizingTeam
    {
        return new ContentArbiterOrganizingTeam();
    }

    public function getContentArbiterForRole(ParticipantRole $role): AbstractContentArbiter
    {
        return match ($role) {
            ParticipantRole::PatrolLeader => $this->getContentArbiterPatrolLeader(),
            ParticipantRole::PatrolParticipant => $this->getContentArbiterPatrolParticipant(),
            ParticipantRole::TroopLeader => $this->getContentArbiterTroopLeader(),
            ParticipantRole::TroopParticipant => $this->getContentArbiterTroopParticipant(),
            ParticipantRole::Ist => $this->getContentArbiterIst(),
            ParticipantRole::Guest => $this->getContentArbiterGuest(),
            ParticipantRole::OrganizingTeam => $this->getContentArbiterOrganizingTeam(),
        };
    }

    public function getMaximalCountForRole(Event $event, ParticipantRole $role): int
    {
        return match ($role) {
            ParticipantRole::PatrolLeader => $event->maximalClosedPatrolsCount ?? 0,
            ParticipantRole::TroopLeader => $event->maximalClosedTroopLeadersCount ?? 0,
            ParticipantRole::TroopParticipant => $event->maximalClosedTroopParticipantsCount ?? 0,
            ParticipantRole::Ist => $event->maximalClosedIstsCount ?? 0,
            ParticipantRole::Guest => $event->maximalClosedGuestsCount ?? 0,
            ParticipantRole::OrganizingTeam => $event->maximalClosedOrganizingTeamCount ?? 0,
            ParticipantRole::PatrolParticipant => $event->maximalPatrolParticipantsCount ?? 0,
        };
    }

    /**
     * @return array<string, string>
     */
    public function getTranslationFilePaths(): array
    {
        return [];
    }

    public function getStylesheetNameWithoutLeadingSlash(): ?string
    {
        return null;
    }

    /**
     * @return list<string>
     */
    public function getFoodOptions(): array
    {
        return [
            'detail.foodWithout',
            'detail.foodVegetarian',
            // 'detail.foodVegan',
            'detail.foodLactoseFree',
            'detail.foodGlutenFree',
            'detail.foodOther',
        ];
    }

    /**
     * @return list<string>
     */
    public function getPositionOptions(): array
    {
        return [
            'detail.position.photo',
            'detail.position.kitchen',
            'detail.position.security',
            'detail.position.hygiene',
            'detail.position.programme',
        ];
    }

    /**
     * @return list<string>
     */
    public function getParticipantCountries(): array
    {
        return [
            'detail.countryCzechRepublic',
            'detail.countrySlovakia',
            'detail.countryOther',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function getLanguages(): array
    {
        return [
            'cs' => '🇨🇿 Česky',
            'sk' => '🇸🇰 Slovensky',
            'en' => '🇬🇧 English',
        ];
    }

    /**
     * @return list<string>
     */
    public function getContingents(): array
    {
        return [];
    }

    /**
     * @return list<string>
     */
    public function getContingentsForAdmin(UserRole $userRole): array
    {
        return [];
    }

    public function showContingentPatrolStats(): bool
    {
        return false;
    }

    public function showFoodStats(): bool
    {
        return true;
    }

    public function isUnlockExpiredButtonAllowed(): bool
    {
        return false;
    }

    public function showParticipantInfoInMail(): bool
    {
        return true;
    }

    public function isMultiplePaymentsAllowed(): bool
    {
        return false;
    }

    public function isLoginEmailAllowed(): bool
    {
        return true;
    }

    public function isLoginSkautisAllowed(): bool
    {
        return false;
    }

    public function enforceActiveSkautisMembership(): bool
    {
        return false;
    }

    protected function isReceiptAllowed(): bool
    {
        return false;
    }

    public function showReceiptToParticipant(Participant $participant): bool
    {
        return
            $this->isReceiptAllowed()
            && $participant->getUserButNotNull()->status === UserStatus::Paid
            && $participant->role !== ParticipantRole::Guest;
    }

    public function getReceiptTemplateName(Participant $participant): string
    {
        return 'receipt/receiptVrj.twig';
    }

    public function getReceiptNumber(string $eventPrefix, Participant $participant, string $paymentId): string
    {
        return sprintf(
            '%s-%s-%s',
            $eventPrefix,
            StringUtils::padWithZeroes((string)$participant->id, 4),
            StringUtils::padWithZeroes($paymentId, 4),
        );
    }

    public function getMinimalPpCount(Event $event, Participant $participant): int
    {
        return $event->minimalPatrolParticipantsCount ?? 0;
    }

    public function getMaximalPpCount(Event $param, Participant $participant): int
    {
        return $param->maximalPatrolParticipantsCount ?? 0;
    }

    public function showIban(): bool
    {
        return false;
    }

    public function showPaymentQrCode(Participant $participant): bool
    {
        return true;
    }

    public function getSkautLogoPath(Participant $participant): string
    {
        return '/SKAUT_horizontalni_logo_250.png';
    }

    public function getSkautStampSignPath(Participant $participant): string
    {
        return '/SkautJunakSignStamp.png';
    }

    /**
     * @return list<EventDeal>
     */
    public function getEventDeals(Participant $participant): array
    {
        return [];
    }

    public function getRosterTemplateName(): string
    {
        return 'roster/roster.twig';
    }
}
