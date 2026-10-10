<?php

declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use kissj\Event\ContentArbiter\ContentArbiterItem;
use kissj\Event\EventType\EventType;
use kissj\Event\EventType\EventTypeDefault;
use kissj\Event\EventType\Obrok\EventTypeObrok;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class OrganizingTeamFieldsTest extends TestCase
{
    /**
     * @return array<string, array{EventType}>
     */
    public static function eventTypes(): array
    {
        return [
            'default' => [new EventTypeDefault()],
            'obrok' => [new EventTypeObrok()],
        ];
    }

    #[DataProvider('eventTypes')]
    public function testPhoneEmailAndFoodAreAllowed(EventType $eventType): void
    {
        $ca = $eventType->getContentArbiterOrganizingTeam();

        self::assertTrue($ca->phone->allowed);
        self::assertTrue($ca->email->allowed);
        self::assertTrue($ca->food->allowed);
    }

    #[DataProvider('eventTypes')]
    public function testFoodOptionsComeFromEventType(EventType $eventType): void
    {
        $ca = $eventType->getContentArbiterOrganizingTeam();

        self::assertSame(
            ContentArbiterItem::selfMappedOptions($eventType->getFoodOptions()),
            $ca->food->options,
        );
    }

    public function testObrokFoodOptionsIncludeVegan(): void
    {
        $ca = (new EventTypeObrok())->getContentArbiterOrganizingTeam();

        self::assertArrayHasKey('detail.foodVegan', $ca->food->options);
    }
}
