<?php

declare(strict_types=1);

namespace Tests\Unit\Participant;

use kissj\Participant\Gender;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class GenderTest extends TestCase
{
    #[DataProvider('skautisDisplayNameProvider')]
    public function testFromSkautisDisplayName(?string $displayName, Gender $expected): void
    {
        self::assertSame($expected, Gender::fromSkautisDisplayName($displayName));
    }

    /**
     * @return array<string, array{?string, Gender}>
     */
    public static function skautisDisplayNameProvider(): array
    {
        return [
            'czech male' => ['muž', Gender::Man],
            'czech female' => ['žena', Gender::Woman],
            'english male' => ['male', Gender::Man],
            'english female' => ['female', Gender::Woman],
            'english man' => ['man', Gender::Man],
            'english woman' => ['woman', Gender::Woman],
            'uppercase czech male' => ['Muž', Gender::Man],
            'uppercase czech female' => ['Žena', Gender::Woman],
            'uppercase english' => ['Male', Gender::Man],
            'null' => [null, Gender::Other],
            'empty string' => ['', Gender::Other],
            'unknown value' => ['unknown', Gender::Other],
        ];
    }

    public function testBackedValues(): void
    {
        self::assertSame('man', Gender::Man->value);
        self::assertSame('woman', Gender::Woman->value);
        self::assertSame('other', Gender::Other->value);
    }
}
