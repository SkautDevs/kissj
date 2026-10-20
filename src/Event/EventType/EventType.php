<?php

declare(strict_types=1);

namespace kissj\Event\EventType;

use kissj\Application\StringUtils;
use kissj\Event\AbstractContentArbiter;
use kissj\Event\ContentArbiter\ContentArbiterItem;
use kissj\Event\ContentArbiterGuest;
use kissj\Event\ContentArbiterIst;
use kissj\Event\ContentArbiterOrganizingTeam;
use kissj\Event\ContentArbiterPatrolLeader;
use kissj\Event\ContentArbiterPatrolParticipant;
use kissj\Event\ContentArbiterTechnicalTeamLeader;
use kissj\Event\ContentArbiterTechnicalTeamParticipant;
use kissj\Event\ContentArbiterTroopLeader;
use kissj\Event\ContentArbiterTroopParticipant;
use kissj\Event\Event;
use kissj\Participant\Guest\Guest;
use kissj\Participant\OrganizingTeam\OrganizingTeam;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRole;
use kissj\Participant\TechnicalTeam\TechnicalTeamLeader;
use kissj\Participant\Troop\TroopLeader;
use kissj\Deal\EventDeal;
use kissj\Payment\FinanceTier;
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

        if ($participant instanceof TechnicalTeamLeader) {
            $pricePerHead = $participant->getUserButNotNull()->event->technicalTeamPrice ?? 0;

            return ($participant->getTechnicalTeamParticipantsCount() + 1) * $pricePerHead;
        }

        return $participant->getUserButNotNull()->event->defaultPrice;
    }

    /**
     * @return list<FinanceTier>
     */
    public function getFinanceTiers(Event $event): array
    {
        return [];
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
        if ($maximumParticipants === null) {
            return false;
        }

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
        $cao = new ContentArbiterOrganizingTeam();
        // food cannot be in ContentArbiterOrganizingTeam because correct food options for the event
        $cao->food->allowed = true;
        $cao->food->options = ContentArbiterItem::selfMappedOptions($this->getFoodOptions());

        return $cao;
    }

    public function getContentArbiterTechnicalTeamLeader(): ContentArbiterTechnicalTeamLeader
    {
        $ca = new ContentArbiterTechnicalTeamLeader();
        $ca->food->options = $this->getFoodOptionsWithNoFood();

        return $ca;
    }

    public function getContentArbiterTechnicalTeamParticipant(): ContentArbiterTechnicalTeamParticipant
    {
        $ca = new ContentArbiterTechnicalTeamParticipant();
        $ca->food->options = $this->getFoodOptionsWithNoFood();

        return $ca;
    }

    /**
     * @return array<string, string>
     */
    private function getFoodOptionsWithNoFood(): array
    {
        return ContentArbiterItem::selfMappedOptions([...$this->getFoodOptions(), Participant::FOOD_NO_EVENT_FOOD]);
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
            ParticipantRole::TechnicalTeamLeader => $this->getContentArbiterTechnicalTeamLeader(),
            ParticipantRole::TechnicalTeamParticipant => $this->getContentArbiterTechnicalTeamParticipant(),
        };
    }

    public function getMaximalCountForRole(Event $event, ParticipantRole $role): ?int
    {
        return match ($role) {
            ParticipantRole::PatrolLeader => $event->maximalClosedPatrolsCount,
            ParticipantRole::TroopLeader => $event->maximalClosedTroopLeadersCount,
            ParticipantRole::TroopParticipant => $event->maximalClosedTroopParticipantsCount,
            ParticipantRole::Ist => $event->maximalClosedIstsCount,
            ParticipantRole::Guest => $event->maximalClosedGuestsCount,
            ParticipantRole::OrganizingTeam => $event->maximalClosedOrganizingTeamCount,
            ParticipantRole::PatrolParticipant => $event->maximalPatrolParticipantsCount,
            ParticipantRole::TechnicalTeamLeader, ParticipantRole::TechnicalTeamParticipant => null,
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

    public function getFaviconNameWithoutLeadingSlash(): string
    {
        return 'favicon-16x16.png';
    }

    public function getAppleTouchIconNameWithoutLeadingSlash(): ?string
    {
        return null;
    }

    public function getThemeColorLight(): string
    {
        return '#255C9E';
    }

    public function getThemeColorDark(): string
    {
        return '#0b1426';
    }

    public function getCelebrationTemplate(): ?string
    {
        return null;
    }

    public function getScriptNameWithoutLeadingSlash(): ?string
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
    public function getSubcamps(): array
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
            && $participant->role !== ParticipantRole::Guest
            && $participant->getAllPaidPayment() !== [];
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
        if ($participant instanceof TroopLeader) {
            return $event->minimalTroopParticipantsCount ?? 0;
        }

        return $event->minimalPatrolParticipantsCount ?? 0;
    }

    public function getMaximalPpCount(Event $event, Participant $participant): ?int
    {
        if ($participant instanceof TroopLeader) {
            return $event->maximalTroopParticipantsCount;
        }

        return $event->maximalPatrolParticipantsCount;
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

    public function isBadgeGenerationAllowed(): bool
    {
        return true;
    }

    public function countContingentsTogetherForCapacity(): bool
    {
        return false;
    }

    public function isOwnerTicketTransferAllowed(): bool
    {
        return false;
    }

    public function isTieCodeShownToIst(): bool
    {
        return false;
    }

    public function getBadgeTemplateName(): string
    {
        return 'badge/badges.twig';
    }

    public function getBlankBadgeTemplateName(): string
    {
        return 'badge/badgeBlank.twig';
    }

    public function getBadgeStylesheetNameWithoutLeadingSlash(): ?string
    {
        return null;
    }

    public function getEmailStylesheetNameWithoutLeadingSlash(): ?string
    {
        return null;
    }
}
