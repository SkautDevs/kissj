<?php declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use kissj\Event\EventType\EventTypeDefault;
use kissj\Event\EventType\Obrok\EventTypeObrok;
use PHPUnit\Framework\TestCase;

class CelebrationTemplateTest extends TestCase
{
    public function testBaseEventTypeReturnsNoCelebrationTemplate(): void
    {
        $eventType = new EventTypeDefault();

        self::assertNull($eventType->getCelebrationTemplate());
    }

    public function testObrokReturnsCelebrationTemplatePath(): void
    {
        $eventType = new EventTypeObrok();

        self::assertSame(
            'widgets/obrok27Celebration.twig',
            $eventType->getCelebrationTemplate(),
        );
    }
}
