<?php

declare(strict_types=1);

namespace Tests\Unit\Participant;

use kissj\Participant\Tshirt;
use PHPUnit\Framework\TestCase;

class TshirtTest extends TestCase
{
    public function testFromStoredParsesShapeAndSize(): void
    {
        $tshirt = Tshirt::fromStored('detail.tshirtGenderMale-detail.tshirtXL');

        self::assertSame('detail.tshirtGenderMale', $tshirt->shape);
        self::assertSame('detail.tshirtXL', $tshirt->size);
    }

    public function testFromStoredHandlesMissingValue(): void
    {
        self::assertNull(Tshirt::fromStored(null)->shape);
        self::assertNull(Tshirt::fromStored(null)->size);
        self::assertNull(Tshirt::fromStored('')->shape);
        self::assertNull(Tshirt::fromStored('')->size);
    }

    public function testFromStoredWithoutDelimiterGivesShapeOnly(): void
    {
        $tshirt = Tshirt::fromStored('detail.tshirtGenderMale');

        self::assertSame('detail.tshirtGenderMale', $tshirt->shape);
        self::assertNull($tshirt->size);
    }

    public function testFromStoredKeepsDashesInsideSize(): void
    {
        $tshirt = Tshirt::fromStored('shape-size-with-dash');

        self::assertSame('shape', $tshirt->shape);
        self::assertSame('size-with-dash', $tshirt->size);
    }

    public function testToStoredRoundTrips(): void
    {
        $stored = (new Tshirt('detail.tshirtGenderMale', 'detail.tshirtXL'))->toStored();

        self::assertSame('detail.tshirtGenderMale-detail.tshirtXL', $stored);
        self::assertSame($stored, Tshirt::fromStored($stored)->toStored());
    }
}
