<?php

declare(strict_types=1);

namespace Tests\Unit\Event;

use kissj\Event\EventType\EventTypeDefault;
use kissj\Event\EventType\Korbo\EventTypeKorbo;
use PHPUnit\Framework\TestCase;

class EventTypeOwnerTransferTest extends TestCase
{
    public function testOwnerTicketTransferIsDisallowedByDefault(): void
    {
        self::assertFalse((new EventTypeDefault())->isOwnerTicketTransferAllowed());
    }

    public function testOwnerTicketTransferIsAllowedForKorbo(): void
    {
        self::assertTrue((new EventTypeKorbo())->isOwnerTicketTransferAllowed());
    }
}
