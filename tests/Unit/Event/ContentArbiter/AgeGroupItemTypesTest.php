<?php

declare(strict_types=1);

namespace Tests\Unit\Event\ContentArbiter;

use kissj\Event\ContentArbiter\ContentArbiterItemType;
use kissj\Event\EventType\Aqua\EventTypeAqua;
use kissj\Event\EventType\Cej\EventTypeCej;
use kissj\Event\EventType\EventType;
use kissj\Event\EventType\EventTypeDefault;
use kissj\Event\EventType\Jj\EventTypeJj;
use kissj\Event\EventType\Korbo\EventTypeKorbo;
use kissj\Event\EventType\Miquik\EventTypeMiquik;
use kissj\Event\EventType\Navigamus\EventTypeNavigamus;
use kissj\Event\EventType\Nsj\EventTypeNsj;
use kissj\Event\EventType\Obrok\EventTypeObrok;
use kissj\Event\EventType\Ospz\EventTypeOspz;
use kissj\Event\EventType\Sfm\EventTypeSfm;
use kissj\Event\EventType\Wsj\EventTypeWsj;
use kissj\Participant\ParticipantRole;
use PHPUnit\Framework\TestCase;

class AgeGroupItemTypesTest extends TestCase
{
    // formFields.twig renders the data-age-group wrapper only for these item types
    public function testAgeGroupIsOnlySetOnTextPhoneAndFileItems(): void
    {
        $supportedTypes = [ContentArbiterItemType::Text, ContentArbiterItemType::Phone, ContentArbiterItemType::File];

        foreach ($this->eventTypes() as $eventType) {
            foreach (ParticipantRole::cases() as $role) {
                foreach ($eventType->getContentArbiterForRole($role)->getAllItems() as $item) {
                    if ($item->ageGroup === null) {
                        continue;
                    }

                    self::assertContains(
                        $item->type,
                        $supportedTypes,
                        $eventType::class . ' ' . $role->value . ' item ' . $item->slug
                            . ' has an ageGroup but type ' . $item->type->value,
                    );
                }
            }
        }
    }

    /**
     * @return list<EventType>
     */
    private function eventTypes(): array
    {
        return [
            new EventTypeAqua(),
            new EventTypeCej(),
            new EventTypeDefault(),
            new EventTypeJj(),
            new EventTypeKorbo(),
            new EventTypeMiquik(),
            new EventTypeNavigamus(),
            new EventTypeNsj(),
            new EventTypeObrok(),
            new EventTypeOspz(),
            new EventTypeSfm(),
            new EventTypeWsj(),
        ];
    }
}
