<?php

declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use kissj\Event\ContentArbiter\ContentArbiterItem;
use kissj\Event\EventType\EventTypeDefault;
use kissj\Event\EventType\Navigamus\EventTypeNavigamus;
use PHPUnit\Framework\TestCase;

class NavigamusOrganizingTeamFieldsTest extends TestCase
{
    public function testArrivalAndDepartureDatesAreAllowedAndRequired(): void
    {
        $ca = (new EventTypeNavigamus())->getContentArbiterOrganizingTeam();

        self::assertTrue($ca->arrivalDate->allowed);
        self::assertTrue($ca->departureDate->allowed);
        self::assertTrue($ca->arrivalDate->required);
        self::assertTrue($ca->departureDate->required);
        self::assertTrue($ca->arrivalDate->editableAfterLock);
        self::assertTrue($ca->departureDate->editableAfterLock);
    }

    public function testPhoneEmailAndFoodStayAllowed(): void
    {
        $eventType = new EventTypeNavigamus();
        $ca = $eventType->getContentArbiterOrganizingTeam();

        self::assertTrue($ca->phone->allowed);
        self::assertTrue($ca->email->allowed);
        self::assertTrue($ca->food->allowed);
        self::assertEquals(ContentArbiterItem::selfMappedOptions($eventType->getFoodOptions()), $ca->food->options);
    }

    public function testUnitIsNotAllowed(): void
    {
        $ca = (new EventTypeNavigamus())->getContentArbiterOrganizingTeam();

        self::assertFalse($ca->unit->allowed);
    }

    public function testDefaultEventTypeOrganizingTeamHasNoDates(): void
    {
        $ca = (new EventTypeDefault())->getContentArbiterOrganizingTeam();

        self::assertFalse($ca->arrivalDate->allowed);
        self::assertFalse($ca->departureDate->allowed);
    }
}
