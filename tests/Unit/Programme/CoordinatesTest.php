<?php

declare(strict_types=1);

namespace Tests\Unit\Programme;

use kissj\Programme\Place\Coordinates;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CoordinatesTest extends TestCase
{
    #[DataProvider('validInputs')]
    public function testParsesCoordinatesAsCopiedFromGoogleMaps(string $input, float $lat, float $lng): void
    {
        $coordinates = Coordinates::tryParse($input);
        self::assertNotNull($coordinates);
        self::assertSame($lat, $coordinates->lat);
        self::assertSame($lng, $coordinates->lng);
    }

    /**
     * @return array<string, array{string, float, float}>
     */
    public static function validInputs(): array
    {
        return [
            'comma and space' => ['50.3408472, 15.9353613', 50.340847, 15.935361],
            'comma only' => ['50.3408472,15.9353613', 50.340847, 15.935361],
            'whitespace with padding' => ['  -33.8688197 151.2092955 ', -33.86882, 151.209296],
            'integers' => ['50, 15', 50.0, 15.0],
            'range borders' => ['90, -180', 90.0, -180.0],
        ];
    }

    #[DataProvider('invalidInputs')]
    public function testRejectsInvalidInput(string $input): void
    {
        self::assertNull(Coordinates::tryParse($input));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidInputs(): array
    {
        return [
            'empty' => [''],
            'blank' => ['   '],
            'text' => ['abc'],
            'single number' => ['50.34'],
            'latitude out of range' => ['91, 15'],
            'longitude out of range' => ['50, 181'],
            'decimal commas' => ['50,34 15,93'],
            'three numbers' => ['50.34, 15.93, 3'],
            'three numbers with semicolons' => ['50.34; 15.93; 1'],
        ];
    }

    public function testFormatsSixDecimals(): void
    {
        self::assertSame('50.340847, 15.935361', (new Coordinates(50.340847, 15.935361))->format());
    }
}
