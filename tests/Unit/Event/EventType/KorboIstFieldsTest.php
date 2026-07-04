<?php

declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use kissj\Event\ContentArbiter\AgeGroup;
use kissj\Event\EventType\Korbo\EventTypeKorbo;
use PHPUnit\Framework\TestCase;

class KorboIstFieldsTest extends TestCase
{
    public function testEmergencyContactIsRestrictedToUnder18(): void
    {
        $ca = (new EventTypeKorbo())->getContentArbiterIst();

        self::assertTrue($ca->emergencyContact->allowed);
        self::assertSame(AgeGroup::Under18, $ca->emergencyContact->ageGroup);
    }

    public function testParentalConsentIsAllowedForUnder18(): void
    {
        $ca = (new EventTypeKorbo())->getContentArbiterIst();

        self::assertTrue($ca->parentalConsent->allowed);
        self::assertSame(AgeGroup::Under18, $ca->parentalConsent->ageGroup);
    }

    public function testAgeGroupItemAppliesToMatchingAge(): void
    {
        $ca = (new EventTypeKorbo())->getContentArbiterIst();

        self::assertTrue($ca->parentalConsent->appliesToAge(15));
        self::assertTrue($ca->parentalConsent->appliesToAge(17));
        self::assertFalse($ca->parentalConsent->appliesToAge(18));
        self::assertFalse($ca->parentalConsent->appliesToAge(40));
    }

    public function testUnboundedItemAppliesToAnyAge(): void
    {
        $ca = (new EventTypeKorbo())->getContentArbiterIst();

        self::assertTrue($ca->phone->appliesToAge(15));
        self::assertTrue($ca->phone->appliesToAge(40));
        self::assertTrue($ca->phone->appliesToAge(null));
    }

    public function testAgeGroupItemAppliesWhenAgeUnknown(): void
    {
        $ca = (new EventTypeKorbo())->getContentArbiterIst();

        self::assertTrue($ca->parentalConsent->appliesToAge(null));
    }
}
