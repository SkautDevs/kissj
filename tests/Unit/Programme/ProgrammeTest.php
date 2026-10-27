<?php

declare(strict_types=1);

namespace Tests\Unit\Programme;

use kissj\Application\DateTimeUtils;
use kissj\Programme\Programme;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProgrammeTest extends TestCase
{
    #[DataProvider('overlapProvider')]
    public function testOverlap(string $startA, string $endA, string $startB, string $endB, bool $expected): void
    {
        $a = $this->createProgramme($startA, $endA);
        $b = $this->createProgramme($startB, $endB);

        // overlapsWith is symmetric, so both directions must agree
        self::assertSame($expected, $a->overlapsWith($b));
        self::assertSame($expected, $b->overlapsWith($a));
    }

    /**
     * @return array<string, array{string, string, string, string, bool}>
     */
    public static function overlapProvider(): array
    {
        return [
            'touching borders do not overlap' =>
                ['2027-06-05 08:00', '2027-06-05 10:00', '2027-06-05 10:00', '2027-06-05 12:00', false],
            'partial overlap' =>
                ['2027-06-05 08:00', '2027-06-05 10:01', '2027-06-05 10:00', '2027-06-05 12:00', true],
            'containment overlaps' =>
                ['2027-06-05 00:00', '2027-06-06 00:00', '2027-06-05 10:00', '2027-06-05 12:00', true],
        ];
    }

    #[DataProvider('exclusivityProvider')]
    public function testExclusivity(
        string $startA,
        string $endA,
        ?string $groupA,
        string $startB,
        string $endB,
        ?string $groupB,
        bool $expected,
    ): void {
        $a = $this->createProgramme($startA, $endA, $groupA);
        $b = $this->createProgramme($startB, $endB, $groupB);

        // isExclusiveWith is symmetric, so both directions must agree
        self::assertSame($expected, $a->isExclusiveWith($b));
        self::assertSame($expected, $b->isExclusiveWith($a));
    }

    /**
     * @return array<string, array{string, string, ?string, string, string, ?string, bool}>
     */
    public static function exclusivityProvider(): array
    {
        return [
            'same group overlapping is exclusive' =>
                [
                    '2027-06-05 08:00',
                    '2027-06-05 10:00',
                    'workshops',
                    '2027-06-05 09:00',
                    '2027-06-05 11:00',
                    'workshops',
                    true,
                ],
            'different group is not exclusive' =>
                [
                    '2027-06-05 08:00',
                    '2027-06-05 10:00',
                    'workshops',
                    '2027-06-05 09:00',
                    '2027-06-05 11:00',
                    'games',
                    false,
                ],
            'grouped vs ungrouped is not exclusive' =>
                [
                    '2027-06-05 08:00',
                    '2027-06-05 10:00',
                    'workshops',
                    '2027-06-05 09:00',
                    '2027-06-05 11:00',
                    null,
                    false,
                ],
            'both ungrouped overlapping is not exclusive' =>
                ['2027-06-05 08:00', '2027-06-05 10:00', null, '2027-06-05 09:30', '2027-06-05 10:30', null, false],
            'same group touching borders is not exclusive' =>
                [
                    '2027-06-05 08:00',
                    '2027-06-05 10:00',
                    'workshops',
                    '2027-06-05 10:00',
                    '2027-06-05 12:00',
                    'workshops',
                    false,
                ],
        ];
    }

    private function createProgramme(string $start, string $end, ?string $groupName = null): Programme
    {
        $programme = new Programme();
        $programme->start = DateTimeUtils::getDateTime($start);
        $programme->end = DateTimeUtils::getDateTime($end);
        $programme->exclusiveGroupName = $groupName;

        return $programme;
    }
}
