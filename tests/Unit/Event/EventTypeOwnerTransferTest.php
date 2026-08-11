<?php

declare(strict_types=1);

namespace Tests\Unit\Event;

use kissj\Event\EventType\EventTypeDefault;
use kissj\Event\EventType\Korbo\EventTypeKorbo;
use kissj\Event\EventType\Obrok\EventTypeObrok;
use PHPUnit\Framework\TestCase;

class EventTypeOwnerTransferTest extends TestCase
{
    public function testOwnerTransferIsDisabledByDefault(): void
    {
        self::assertFalse((new EventTypeDefault())->isOwnerTicketTransferAllowed());
    }

    public function testOwnerTransferIsEnabledForKorboAndObrok(): void
    {
        self::assertTrue((new EventTypeKorbo())->isOwnerTicketTransferAllowed());
        self::assertTrue((new EventTypeObrok())->isOwnerTicketTransferAllowed());
    }
}
