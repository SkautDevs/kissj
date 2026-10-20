<?php

declare(strict_types=1);

namespace Tests\Unit\Templates;

use PHPUnit\Framework\TestCase;

class BaseThemeTest extends TestCase
{
    private const PUBLIC_DIR = __DIR__ . '/../../../public';

    public function testMozaikaTilesAreSeamlessSquaresOnTheirPageColour(): void
    {
        $light = file_get_contents(self::PUBLIC_DIR . '/background_mozaika_light.svg');
        $dark = file_get_contents(self::PUBLIC_DIR . '/background_mozaika_dark.svg');

        self::assertIsString($light);
        self::assertIsString($dark);
        self::assertStringStartsWith('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400"', $light);
        self::assertStringStartsWith('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400"', $dark);
        self::assertStringContainsString('<rect width="400" height="400" fill="#ffffff"/>', $light);
        self::assertStringContainsString('<rect width="400" height="400" fill="#0b1426"/>', $dark);
        self::assertStringContainsString('transform="translate(', $light);
        self::assertStringContainsString('transform="translate(', $dark);
    }

    public function testCej26KeepsNeutralDarkCards(): void
    {
        $cej = file_get_contents(self::PUBLIC_DIR . '/eventSpecificCss/stylesCej26.css');
        self::assertIsString($cej);

        self::assertStringContainsString('--color-card-background: #252525;', $cej);
        self::assertStringContainsString('--color-input-background: #2f2f2f;', $cej);
    }

    public function testStylesheetUsesTheMozaikaTilesAndSkautTokens(): void
    {
        $css = file_get_contents(self::PUBLIC_DIR . '/styles.css');
        self::assertIsString($css);

        self::assertStringContainsString('background-image: url("/background_mozaika_light.svg");', $css);
        self::assertStringContainsString('background-image: url("/background_mozaika_dark.svg");', $css);
        self::assertStringNotContainsString('background_shapes_2016', $css);
        self::assertFileDoesNotExist(self::PUBLIC_DIR . '/background_shapes_2016_light_600.png');
        self::assertFileDoesNotExist(self::PUBLIC_DIR . '/background_shapes_2016_dark_600.png');

        self::assertStringContainsString('--color-main: #255C9E;', $css);
        self::assertStringContainsString('--color-main: #6B96CA;', $css);
        self::assertStringContainsString('--color-background: #0b1426;', $css);
        self::assertStringNotContainsString('#0D71B9', $css);
        self::assertStringNotContainsString('#85A2D5', $css);
    }

    public function testTableStripesHaveTheirOwnColour(): void
    {
        $css = file_get_contents(self::PUBLIC_DIR . '/styles.css');
        self::assertIsString($css);

        self::assertStringContainsString("tr:nth-child(even) {\n\tbackground: var(--color-stripe);\n}", $css);
        self::assertStringContainsString('--color-stripe: #ebf2f5;', $css);
        self::assertStringContainsString('--color-stripe: #0b1426;', $css);
    }

    public function testAlertsCarryTheirSignalColourAsATopStrip(): void
    {
        $css = file_get_contents(self::PUBLIC_DIR . '/styles.css');
        self::assertIsString($css);

        self::assertStringContainsString('border-top: 4px solid #007BC2;', $css);
        self::assertStringContainsString('border-top: 4px solid #89BA17;', $css);
        self::assertStringContainsString('border-top: 4px solid #FFCC00;', $css);
        self::assertStringContainsString('border-top: 4px solid #E53434;', $css);
        self::assertDoesNotMatchRegularExpression('/\.alert \{[^}]*box-shadow/', $css);
    }

    public function testMailLayoutUsesTheSameSkautTokens(): void
    {
        $mail = file_get_contents(__DIR__ . '/../../../src/Templates/translatable/emails/_mail-layout.twig');
        self::assertIsString($mail);

        self::assertStringContainsString('--color-main: #255C9E;', $mail);
        self::assertStringContainsString('--color-link: #172983;', $mail);
        self::assertStringContainsString('--color-link-visited: #5E2281;', $mail);
        self::assertStringContainsString('--color-main: #6B96CA;', $mail);
        self::assertStringContainsString('--color-link: #BDD4DF;', $mail);
        self::assertStringContainsString('--color-link-visited: #B98ED9;', $mail);
        self::assertStringNotContainsString('#0D71B9', $mail);
        self::assertStringNotContainsString('#85A2D5', $mail);
    }
}
