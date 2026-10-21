<?php

declare(strict_types=1);

namespace Tests\Unit\Settings;

use PHPUnit\Framework\TestCase;

class PhinxConfigurationTest extends TestCase
{
    public function testDotenvIsLoadedFromRepositoryRoot(): void
    {
        $settingsDir = __DIR__ . '/../../../src/Settings';
        $source = (string)file_get_contents($settingsDir . '/phinxConfiguration.php');

        self::assertSame(1, preg_match('/Dotenv::createImmutable\(__DIR__ \. \'([^\']+)\'\)/', $source, $matches));
        self::assertFileExists($settingsDir . $matches[1] . 'composer.json');
    }
}
