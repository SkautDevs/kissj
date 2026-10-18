<?php

declare(strict_types=1);

namespace Tests\Unit\Settings;

use LogicException;
use PHPUnit\Framework\TestCase;
use Tests\Support\ProjectFiles;

class EnvExampleTest extends TestCase
{
    private const array SET_BY_SETTINGS = ['APP_NAME', 'DATABASE_PATH'];

    public function testEnvExampleListsExactlyTheVariablesTheAppReads(): void
    {
        $read = array_unique([
            ...$this->namesIn('/src/Settings/EnvSettings.php', '/self::string\(\$env, \'([A-Z0-9_]+)\'/'),
            ...$this->namesIn('/src/Settings/phinxConfiguration.php', '/\$_ENV\[\'([A-Z0-9_]+)\'\]/'),
        ]);
        $expected = array_values(array_diff($read, self::SET_BY_SETTINGS));
        sort($expected);

        $documented = $this->namesIn('/.env.example', '/^([A-Z0-9_]+)=/m');
        sort($documented);

        self::assertSame($expected, $documented, '.env.example must list exactly the variables the app reads');
    }

    /**
     * @return list<string>
     */
    private function namesIn(string $relativePath, string $pattern): array
    {
        $content = file_get_contents(ProjectFiles::ROOT . $relativePath);
        if ($content === false) {
            throw new LogicException('Cannot read ' . $relativePath);
        }

        preg_match_all($pattern, $content, $matches);

        return $matches[1];
    }
}
