<?php

declare(strict_types=1);

namespace Tests\Unit\Templates;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class PsychicalIssuesTranslationsTest extends TestCase
{
    public function testPsychicalIssuesLabel(): void
    {
        $expected = [
            'cs' => 'Psychická omezení',
            'sk' => 'psychické obmedzenia',
            'en' => 'Mental health limitations',
        ];

        foreach ($expected as $locale => $label) {
            $translations = Yaml::parseFile(__DIR__ . '/../../../src/Templates/' . $locale . '.yaml');
            self::assertIsArray($translations);
            self::assertIsArray($translations['detail']);
            self::assertSame($label, $translations['detail']['psychicalIssues']);
        }
    }
}
