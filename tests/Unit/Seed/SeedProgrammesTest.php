<?php

declare(strict_types=1);

namespace Tests\Unit\Seed;

use DateTimeImmutable;
use kissj\Application\ImageUtils;
use kissj\Application\migrations\seeds\programmes\Korbo3026Programmes;
use kissj\Application\migrations\seeds\programmes\Miquik3026Programmes;
use kissj\Application\migrations\seeds\programmes\Navigamus3027Programmes;
use kissj\Application\migrations\seeds\programmes\Obrok3027Programmes;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SeedProgrammesTest extends TestCase
{
    /**
     * @return array<string, array{class-string, string, string, int, list<string>}>
     */
    public static function seedProvider(): array
    {
        return [
            'navigamus' => [Navigamus3027Programmes::class, '2027-06-03', '2027-06-06', 8, ['ist']],
            'korbo' => [Korbo3026Programmes::class, '2026-11-13', '2026-11-17', 59, ['ist']],
            'miquik' => [Miquik3026Programmes::class, '2026-11-13', '2026-11-15', 41, ['pl', 'ist']],
            'obrok' => [Obrok3027Programmes::class, '2027-05-27', '2027-05-31', 204, ['tl', 'ist']],
        ];
    }

    /**
     * @param class-string $class
     * @param list<string> $allowedRoles
     */
    #[DataProvider('seedProvider')]
    public function testProgrammesAreConsistent(
        string $class,
        string $startDay,
        string $endDay,
        int $expectedCount,
        array $allowedRoles,
    ): void {
        /**
         * @var list<array{
         *     name: string,
         *     description: string,
         *     place: string,
         *     start: string,
         *     end: string,
         *     capacity: int,
         *     isPreregistered: bool,
         *     roles: list<string>,
         * }> $programmes
         */
        $programmes = $class::PROGRAMMES;
        self::assertCount($expectedCount, $programmes);

        $earliest = $startDay . ' 00:00:00';
        $latest = $endDay . ' 23:59:59';

        foreach ($programmes as $index => $programme) {
            $start = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $programme['start']);
            $end = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $programme['end']);
            self::assertInstanceOf(DateTimeImmutable::class, $start);
            self::assertInstanceOf(DateTimeImmutable::class, $end);
            self::assertLessThan($end, $start);
            self::assertGreaterThanOrEqual($earliest, $programme['start']);
            self::assertLessThanOrEqual($latest, $programme['end']);

            self::assertNotSame([], $programme['roles']);
            self::assertSame([], array_diff($programme['roles'], $allowedRoles));
            self::assertSame([2, 5, 30][$index % 3], $programme['capacity']);
            self::assertNotSame('', $programme['name']);
        }
    }

    public function testMultilineDescriptionsAreKept(): void
    {
        $hasMultiline = false;
        foreach (Miquik3026Programmes::PROGRAMMES as $programme) {
            $hasMultiline = $hasMultiline || str_contains($programme['description'], "\n");
        }

        self::assertTrue($hasMultiline);
    }

    public function testObrokImagesExistAreValidAndFollowTheThreeOfFourRule(): void
    {
        $withImage = 0;
        $withoutImage = 0;
        foreach (Obrok3027Programmes::PROGRAMMES as $index => $programme) {
            if ($index % 4 === 3) {
                self::assertNull($programme['image'], 'index ' . $index);
                $withoutImage++;
                continue;
            }

            self::assertNotNull($programme['image'], 'index ' . $index);
            $path = __DIR__ . '/../../../src/Application/migrations/seeds/programmes/images/' . $programme['image'];
            self::assertFileExists($path);
            $contents = file_get_contents($path);
            self::assertIsString($contents);
            self::assertTrue(ImageUtils::isSupportedRasterImage($contents), $programme['image']);
            $withImage++;
        }

        self::assertSame(153, $withImage);
        self::assertSame(51, $withoutImage);
    }

    public function testOtherEventsHaveNoImages(): void
    {
        $programmesByEvent = [
            Navigamus3027Programmes::PROGRAMMES,
            Korbo3026Programmes::PROGRAMMES,
            Miquik3026Programmes::PROGRAMMES,
        ];

        foreach ($programmesByEvent as $programmes) {
            foreach ($programmes as $programme) {
                self::assertArrayNotHasKey('image', $programme);
            }
        }
    }
}
