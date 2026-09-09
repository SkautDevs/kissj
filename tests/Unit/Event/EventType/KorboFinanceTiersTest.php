<?php

declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use kissj\Event\Event;
use kissj\Event\EventType\EventTypeDefault;
use kissj\Event\EventType\Korbo\EventTypeKorbo;
use kissj\Payment\FinanceTier;
use kissj\Payment\ScarfFinanceTier;
use PHPUnit\Framework\TestCase;

class KorboFinanceTiersTest extends TestCase
{
    public function testKorboHasFourTiersDerivedFromDefaultPrice(): void
    {
        $event = new Event(['defaultPrice' => 450]);

        $tiers = (new EventTypeKorbo())->getFinanceTiers($event);

        self::assertCount(4, $tiers);
        self::assertSame([
            ['price' => 450, 'scarf' => false],
            ['price' => 600, 'scarf' => false],
            ['price' => 600, 'scarf' => true],
            ['price' => 750, 'scarf' => true],
        ], array_map(
            static fn (ScarfFinanceTier $tier): array => ['price' => $tier->price, 'scarf' => $tier->scarf],
            $tiers,
        ));
    }

    public function testDefaultEventTypeHasSingleDefaultPriceTier(): void
    {
        $event = new Event(['defaultPrice' => 450]);

        $tiers = (new EventTypeDefault())->getFinanceTiers($event);

        self::assertCount(1, $tiers);
        self::assertSame(FinanceTier::class, $tiers[0]::class);
        self::assertSame(450, $tiers[0]->price);
    }
}
