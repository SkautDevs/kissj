<?php

declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use kissj\Event\Event;
use kissj\Event\EventType\EventTypeDefault;
use kissj\Event\EventType\Korbo\EventTypeKorbo;
use PHPUnit\Framework\TestCase;

class KorboFinanceTiersTest extends TestCase
{
    public function testKorboHasFourTiersDerivedFromDefaultPrice(): void
    {
        $event = new Event(['defaultPrice' => 450]);

        $tiers = (new EventTypeKorbo())->getFinanceTiers($event);

        self::assertSame([
            ['price' => 450, 'scarf' => false],
            ['price' => 600, 'scarf' => false],
            ['price' => 600, 'scarf' => true],
            ['price' => 750, 'scarf' => true],
        ], $tiers);
    }

    public function testDefaultEventTypeHasNoTiers(): void
    {
        $event = new Event(['defaultPrice' => 450]);

        self::assertSame([], (new EventTypeDefault())->getFinanceTiers($event));
    }
}
