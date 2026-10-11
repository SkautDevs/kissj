<?php

declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use kissj\Event\ContentArbiter\ContentArbiterItem;
use kissj\Event\EventType\EventTypeDefault;
use kissj\Event\EventType\Navigamus\EventTypeNavigamus;
use kissj\Event\EventType\Obrok\EventTypeObrok;
use PHPUnit\Framework\TestCase;

class TechnicalTeamFieldsTest extends TestCase
{
    public function testParticipantAllowsOnlyAgreedFields(): void
    {
        $ca = (new EventTypeDefault())->getContentArbiterTechnicalTeamParticipant();
        $slugs = array_map(fn (ContentArbiterItem $item): string => $item->slug, $ca->getAllowedItems());

        self::assertSame(
            ['firstName', 'lastName', 'nickname', 'foodPreferences', 'scarf', 'arrivalDate', 'departureDate'],
            $slugs,
        );
        self::assertTrue($ca->firstName->required);
        self::assertTrue($ca->lastName->required);
        self::assertFalse($ca->nickname->required);
        self::assertTrue($ca->food->required);
        self::assertFalse($ca->scarf->required);
        self::assertTrue($ca->arrivalDate->required);
        self::assertTrue($ca->departureDate->required);
    }

    public function testLeaderAddsContactAndTeamName(): void
    {
        $ca = (new EventTypeDefault())->getContentArbiterTechnicalTeamLeader();
        $slugs = array_map(fn (ContentArbiterItem $item): string => $item->slug, $ca->getAllowedItems());

        self::assertSame(
            [
                'patrolName',
                'firstName',
                'lastName',
                'nickname',
                'email',
                'telephoneNumber',
                'foodPreferences',
                'scarf',
                'arrivalDate',
                'departureDate',
            ],
            $slugs,
        );
        self::assertSame('detail.technicalTeamName', $ca->patrolName->label);
    }

    public function testFoodOptionsStartWithEmptyPromptAndEndWithNoEventFood(): void
    {
        foreach ([new EventTypeObrok(), new EventTypeNavigamus()] as $eventType) {
            $options = $eventType->getContentArbiterTechnicalTeamParticipant()->food->options;

            self::assertSame('', array_key_first($options));
            self::assertSame('detail.foodChoose', $options['']);
            self::assertSame('detail.foodNoEventFood', array_key_last($options));
            foreach ($eventType->getFoodOptions() as $eventFoodOption) {
                self::assertArrayHasKey($eventFoodOption, $options);
            }
            self::assertSame($options, $eventType->getContentArbiterTechnicalTeamLeader()->food->options);
        }
    }

    public function testScarfUsesTechnicalTeamWordingWithoutPrice(): void
    {
        $ca = (new EventTypeDefault())->getContentArbiterTechnicalTeamParticipant();

        self::assertSame(
            ['' => 'detail.foodChoose', 'yes' => 'detail.scarfTechnicalTeamYes', 'no' => 'detail.scarfNo'],
            $ca->scarf->options,
        );
    }
}
