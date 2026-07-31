<?php

declare(strict_types=1);

namespace Tests\Unit\Translation;

use FilesystemIterator;
use LogicException;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class TranslationKeysTest extends TestCase
{
    /** @var list<string> */
    private const array BASE_LOCALES = ['cs', 'en', 'sk'];
    private const string PROJECT_DIR = __DIR__ . '/../../..';
    private const string TEMPLATES_DIR = self::PROJECT_DIR . '/src/Templates';
    private const string EVENT_TYPES_GLOB = self::PROJECT_DIR . '/src/Event/EventType/*/*.yaml';

    // matches 'key' | trans and 'key' | transGendered with any spacing around the pipe;
    // both quote styles are accepted so a key cannot escape the check by being requoted,
    // while an interpolated key such as ('deal.' ~ slug)|trans deliberately does not match
    private const string KEY_PATTERN = '/([\'"])([^\'"]*)\1\s*\|\s*trans(?:Gendered)?\b/';

    // transGendered falls back to the base key when the gendered variant is absent,
    // so .man/.woman strings are legitimately Czech-only
    private const string GENDER_SUFFIX_PATTERN = '/\.(man|woman)$/';

    public function testEveryTwigTranslationKeyResolves(): void
    {
        $base = $this->loadBaseTranslations();
        $eventKeys = $this->loadEventTypeKeys();

        $unresolved = [];
        foreach ($this->twigFilePaths() as $path) {
            $contents = file_get_contents($path);
            if ($contents === false) {
                throw new LogicException('Cannot read twig template ' . $path);
            }

            foreach (explode("\n", $contents) as $index => $line) {
                $matchCount = preg_match_all(self::KEY_PATTERN, $line, $matches);
                if ($matchCount === false) {
                    throw new LogicException('Translation key pattern failed to compile');
                }

                if ($matchCount === 0) {
                    continue;
                }

                foreach ($matches[2] as $key) {
                    $reason = $this->reasonKeyIsUnresolvable($key, $base, $eventKeys);
                    if ($reason === null) {
                        continue;
                    }

                    $unresolved[] = sprintf(
                        '%s:%d "%s" — %s',
                        $this->relativePath($path),
                        $index + 1,
                        $key,
                        $reason,
                    );
                }
            }
        }

        self::assertSame(
            [],
            $unresolved,
            "Twig templates reference translation keys that do not resolve:\n"
            . implode("\n", $unresolved),
        );
    }

    public function testEveryBaseKeyIsDefinedInAllLocales(): void
    {
        $base = $this->loadBaseTranslations();

        $allKeys = [];
        foreach ($base as $keys) {
            $allKeys += $keys;
        }
        ksort($allKeys);

        $gaps = [];
        foreach (array_keys($allKeys) as $key) {
            if (preg_match(self::GENDER_SUFFIX_PATTERN, $key) === 1) {
                continue;
            }

            $missing = [];
            foreach (self::BASE_LOCALES as $locale) {
                if (!array_key_exists($key, $base[$locale])) {
                    $missing[] = $locale;
                }
            }

            if ($missing !== []) {
                $gaps[] = sprintf('"%s" — missing in %s', $key, implode(', ', $missing));
            }
        }

        self::assertSame(
            [],
            $gaps,
            "Translation keys are not defined in every base locale:\n" . implode("\n", $gaps),
        );
    }

    /**
     * @param array<string, array<string, true>> $base
     * @param array<string, true> $eventKeys
     */
    private function reasonKeyIsUnresolvable(string $key, array $base, array $eventKeys): ?string
    {
        $missing = [];
        $present = [];
        foreach (self::BASE_LOCALES as $locale) {
            if (array_key_exists($key, $base[$locale])) {
                $present[] = $locale;
            } else {
                $missing[] = $locale;
            }
        }

        if ($missing === []) {
            return null;
        }

        // a key defined in any base locale belongs to the base files, so the event-type
        // files must not excuse it missing from the others
        if ($present !== []) {
            return 'defined in ' . implode(', ', $present) . ' but missing in ' . implode(', ', $missing);
        }

        if (array_key_exists($key, $eventKeys)) {
            return null;
        }

        return 'not defined in any translation file';
    }

    /**
     * @return array<string, array<string, true>>
     */
    private function loadBaseTranslations(): array
    {
        $base = [];
        foreach (self::BASE_LOCALES as $locale) {
            $base[$locale] = $this->flattenKeys(
                $this->parseYamlFile(self::TEMPLATES_DIR . '/' . $locale . '.yaml'),
            );
        }

        return $base;
    }

    /**
     * @return array<string, true>
     */
    private function loadEventTypeKeys(): array
    {
        $paths = glob(self::EVENT_TYPES_GLOB);
        if ($paths === false) {
            throw new LogicException('Cannot list event type translation files');
        }

        $keys = [];
        foreach ($paths as $path) {
            $keys += $this->flattenKeys($this->parseYamlFile($path));
        }

        return $keys;
    }

    /**
     * @param array<mixed> $data
     * @return array<string, true>
     */
    private function flattenKeys(array $data, string $prefix = ''): array
    {
        $keys = [];
        foreach ($data as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;
            if (is_array($value)) {
                $keys += $this->flattenKeys($value, $path);
            } else {
                $keys[$path] = true;
            }
        }

        return $keys;
    }

    /**
     * @return array<mixed>
     */
    private function parseYamlFile(string $path): array
    {
        $parsed = Yaml::parseFile($path);
        if (!is_array($parsed)) {
            throw new LogicException('Translation file is not a yaml mapping: ' . $path);
        }

        return $parsed;
    }

    /**
     * @return list<string>
     */
    private function twigFilePaths(): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(self::TEMPLATES_DIR, FilesystemIterator::SKIP_DOTS),
        );

        $paths = [];
        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo || $file->getExtension() !== 'twig') {
                continue;
            }

            // TEMPLATES_DIR is relative to this test file, so paths must be resolved
            // before relativePath() can strip the project prefix off them
            $realPath = $file->getRealPath();
            if ($realPath === false) {
                throw new LogicException('Cannot resolve twig template path ' . $file->getPathname());
            }

            $paths[] = $realPath;
        }

        sort($paths);

        return $paths;
    }

    private function relativePath(string $path): string
    {
        $projectDir = realpath(self::PROJECT_DIR);
        if ($projectDir === false) {
            throw new LogicException('Cannot resolve project directory');
        }

        return str_replace($projectDir . '/', '', $path);
    }
}
