<?php

declare(strict_types=1);

namespace Tests\Unit\Participant;

use kissj\Participant\Gender;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class GenderTest extends TestCase
{
    #[DataProvider('skautisIdSexProvider')]
    public function testFromSkautisIdSex(?string $idSex, Gender $expected): void
    {
        self::assertSame($expected, Gender::fromSkautisIdSex($idSex));
    }

    /**
     * @return array<string, array{?string, Gender}>
     */
    public static function skautisIdSexProvider(): array
    {
        return [
            'male' => ['male', Gender::Man],
            'female' => ['female', Gender::Woman],
            'null' => [null, Gender::Other],
            'empty string' => ['', Gender::Other],
            'display name is not accepted' => ['Muž', Gender::Other],
            'unknown value' => ['unknown', Gender::Other],
        ];
    }

    public function testBackedValues(): void
    {
        self::assertSame('man', Gender::Man->value);
        self::assertSame('woman', Gender::Woman->value);
        self::assertSame('other', Gender::Other->value);
    }

    #[DataProvider('emailSuffixProvider')]
    public function testToEmailSuffix(Gender $gender, string $expected): void
    {
        self::assertSame($expected, $gender->toEmailSuffix());
    }

    /**
     * @return array<string, array{Gender, string}>
     */
    public static function emailSuffixProvider(): array
    {
        return [
            'man'   => [Gender::Man,   '.man'],
            'woman' => [Gender::Woman, '.woman'],
            'other' => [Gender::Other, ''],
        ];
    }
}
