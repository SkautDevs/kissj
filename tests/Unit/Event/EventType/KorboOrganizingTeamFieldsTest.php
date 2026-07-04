<?php

declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use kissj\Event\ContentArbiter\AgeGroup;
use kissj\Event\EventType\Korbo\EventTypeKorbo;
use PHPUnit\Framework\TestCase;

class KorboOrganizingTeamFieldsTest extends TestCase
{
    public function testPhoneAndEmailAreAllowed(): void
    {
        $ca = (new EventTypeKorbo())->getContentArbiterOrganizingTeam();

        self::assertTrue($ca->phone->allowed);
        self::assertTrue($ca->email->allowed);
    }

    public function testCountryIsAllowedWithMappedOptions(): void
    {
        $ca = (new EventTypeKorbo())->getContentArbiterOrganizingTeam();

        self::assertTrue($ca->country->allowed);
        self::assertNotSame([], $ca->country->options);
    }

    public function testEmergencyContactIsRestrictedToUnder18(): void
    {
        $ca = (new EventTypeKorbo())->getContentArbiterOrganizingTeam();

        self::assertTrue($ca->emergencyContact->allowed);
        self::assertSame(AgeGroup::Under18, $ca->emergencyContact->ageGroup);
    }

    public function testParentalConsentIsRequiredForUnder18(): void
    {
        $ca = (new EventTypeKorbo())->getContentArbiterOrganizingTeam();

        self::assertTrue($ca->parentalConsent->allowed);
        self::assertTrue($ca->parentalConsent->required);
        self::assertSame(AgeGroup::Under18, $ca->parentalConsent->ageGroup);
    }

    public function testScarfIsAllowedAndOrderedAfterDocuments(): void
    {
        $ca = (new EventTypeKorbo())->getContentArbiterOrganizingTeam();

        self::assertTrue($ca->scarf->allowed);
        self::assertSame(410, $ca->scarf->order);
    }

    public function testGenderIsDisabled(): void
    {
        $ca = (new EventTypeKorbo())->getContentArbiterOrganizingTeam();

        self::assertFalse($ca->gender->allowed);
    }

    public function testUnitIsAllowed(): void
    {
        $ca = (new EventTypeKorbo())->getContentArbiterOrganizingTeam();

        self::assertTrue($ca->unit->allowed);
    }
}
