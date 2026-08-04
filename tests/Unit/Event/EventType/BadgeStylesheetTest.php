<?php

declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use kissj\Event\EventType\EventTypeDefault;
use kissj\Event\EventType\Obrok\EventTypeObrok;
use PHPUnit\Framework\TestCase;

class BadgeStylesheetTest extends TestCase
{
    public function testBaseEventTypeReturnsNoBadgeStylesheet(): void
    {
        $eventType = new EventTypeDefault();

        self::assertNull($eventType->getBadgeStylesheetNameWithoutLeadingSlash());
    }

    public function testObrokReturnsBadgeStylesheetPath(): void
    {
        $eventType = new EventTypeObrok();

        self::assertSame(
            'eventSpecificCss/badgeObrok27.css',
            $eventType->getBadgeStylesheetNameWithoutLeadingSlash(),
        );
    }

    // mPdfGenerator loads this by name off the filesystem, so a typo would only surface as an
    // unstyled badge in production
    public function testObrokBadgeStylesheetFileExists(): void
    {
        $eventType = new EventTypeObrok();

        self::assertFileExists(
            __DIR__ . '/../../../../public/' . $eventType->getBadgeStylesheetNameWithoutLeadingSlash(),
        );
    }
}
