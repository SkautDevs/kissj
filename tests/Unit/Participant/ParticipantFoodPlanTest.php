<?php

declare(strict_types=1);

namespace Tests\Unit\Participant;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Participant\Ist\Ist;
use kissj\Participant\ParticipantFoodPlan;
use kissj\Participant\ParticipantRole;
use PHPUnit\Framework\TestCase;

class ParticipantFoodPlanTest extends TestCase
{
    public function testDatesCoverEventDaysInclusive(): void
    {
        $plan = new ParticipantFoodPlan([], $this->createEvent());

        self::assertSame(['13.11.', '14.11.', '15.11.'], $plan->roleAggregatedToArray()['dates']);
    }

    public function testParticipantArrivalAndDepartureDaysAreCounted(): void
    {
        $ist = new Ist();
        $ist->role = ParticipantRole::Ist;
        $ist->foodPreferences = 'detail.foodWithout';
        $ist->arrivalDate = DateTimeUtils::getDateTime('2026-11-12');
        $ist->departureDate = DateTimeUtils::getDateTime('2026-11-16');

        $plan = new ParticipantFoodPlan([$ist], $this->createEvent());
        $array = $plan->roleAggregatedToArray();

        $expectedDays = ['12.11.', '13.11.', '14.11.', '15.11.', '16.11.'];
        self::assertSame($expectedDays, $array['dates']);
        $istRow = $array['rows']['role.ist'];
        self::assertIsArray($istRow);
        self::assertSame($expectedDays, array_keys($istRow));
        foreach ($expectedDays as $day) {
            self::assertSame(1, $istRow[$day]['detail.foodWithout']);
        }
    }

    public function testArrivalDayStoredWithUtcOffsetIsCounted(): void
    {
        $ist = new Ist();
        $ist->role = ParticipantRole::Ist;
        $ist->foodPreferences = 'detail.foodWithout';
        $ist->arrivalDate = DateTimeUtils::getDateTime('2026-11-14T00:00:00+00:00');
        $ist->departureDate = DateTimeUtils::getDateTime('2026-11-15T00:00:00+00:00');

        $istRow = (new ParticipantFoodPlan([$ist], $this->createEvent()))->roleAggregatedToArray()['rows']['role.ist'];

        self::assertIsArray($istRow);
        self::assertSame(['14.11.', '15.11.'], array_keys($istRow));
    }

    private function createEvent(): Event
    {
        $event = new Event();
        $event->startDay = DateTimeUtils::getDateTime('2026-11-13');
        $event->endDay = DateTimeUtils::getDateTime('2026-11-15');

        return $event;
    }
}
