<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CssContrastTest extends TestCase
{
    private const float AA_NORMAL_TEXT = 4.5;

    private const array NAMED_COLORS = [
        'white' => '#ffffff',
        'black' => '#000000',
        'lightskyblue' => '#87cefa',
    ];

    public function testBaseStylesHasFocusVisibleAndReducedMotion(): void
    {
        $css = $this->readCss('public/styles.css');

        self::assertStringContainsString(':focus-visible', $css, 'public/styles.css must define :focus-visible styles');
        self::assertStringContainsString(
            'prefers-reduced-motion: reduce',
            $css,
            'public/styles.css must respect prefers-reduced-motion: reduce',
        );
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function stylesheetProvider(): iterable
    {
        foreach (['light', 'dark'] as $scheme) {
            yield 'base-' . $scheme => ['public/styles.css', $scheme];
            yield 'navigamus27-' . $scheme => ['public/eventSpecificCss/stylesNavigamus27.css', $scheme];
            yield 'obrok27-' . $scheme => ['public/eventSpecificCss/stylesObrok27.css', $scheme];
        }
    }

    #[DataProvider('stylesheetProvider')]
    public function testPaletteMeetsWcagAaContrast(string $cssPath, string $scheme): void
    {
        $baseCss = $this->readCss('public/styles.css');
        $baseVars = $this->extractRootVars($baseCss, $scheme);
        $baseAlerts = $this->extractAlerts($baseCss, $scheme, $baseVars);

        if ($cssPath === 'public/styles.css') {
            $vars = $baseVars;
            $alerts = $baseAlerts;
        } else {
            $eventCss = $this->readCss($cssPath);
            $vars = array_merge($baseVars, $this->extractRootVars($eventCss, $scheme));
            $alerts = array_merge($baseAlerts, $this->extractAlerts($eventCss, $scheme, $vars));
        }

        $pairs = [
            'main on card' => [$vars['--color-main'], $vars['--color-card-background']],
            'main on background' => [$vars['--color-main'], $vars['--color-background']],
            'btn text on main' => [$vars['--color-card-background'], $vars['--color-main']],
            'btn-grey text on grey' => [$vars['--color-card-background'], $vars['--color-grey']],
            'accent on card' => [$vars['--color-accent'], $vars['--color-card-background']],
            'inactive step (light) on background' => [$vars['--color-light'], $vars['--color-background']],
            'input text (dark) on input bg' => [$vars['--color-dark'], $vars['--color-input-background']],
            'muted text (grey) on card' => [$vars['--color-grey'], $vars['--color-card-background']],
            'link on card' => [$vars['--color-link'], $vars['--color-card-background']],
            'visited link on card' => [$vars['--color-link-visited'], $vars['--color-card-background']],
        ];

        foreach ($pairs as $label => [$fg, $bg]) {
            $ratio = $this->contrastRatio($fg, $bg);
            self::assertGreaterThanOrEqual(
                self::AA_NORMAL_TEXT,
                $ratio,
                sprintf('[%s %s] %s (%s on %s) contrast %.2f < 4.50', $cssPath, $scheme, $label, $fg, $bg, $ratio),
            );
        }

        foreach ($alerts as $alertName => [$fg, $bg]) {
            $ratio = $this->contrastRatio($fg, $bg);
            self::assertGreaterThanOrEqual(
                self::AA_NORMAL_TEXT,
                $ratio,
                sprintf('[%s %s] .%s (%s on %s) contrast %.2f < 4.50', $cssPath, $scheme, $alertName, $fg, $bg, $ratio),
            );
        }
    }

    private function readCss(string $relativePath): string
    {
        $content = file_get_contents(__DIR__ . '/../../../' . $relativePath);
        self::assertIsString($content, 'Could not read ' . $relativePath);

        return $content;
    }

    /**
     * @return array<string, string>
     */
    private function extractRootVars(string $css, string $scheme): array
    {
        $vars = [];

        // Extract top-level :root palette variables (e.g. --o27-dark) before @media blocks
        $beforeMedia = preg_split('/@media\b/', $css, 2);
        if (is_array($beforeMedia) && preg_match('/:root\s*\{([^}]*)\}/s', $beforeMedia[0], $topMatch) === 1) {
            $topCount = preg_match_all('/(--[a-z0-9-]+)\s*:\s*([^;]+);/', $topMatch[1], $topMatches, PREG_SET_ORDER);
            if (is_int($topCount) && $topCount > 0) {
                foreach ($topMatches as $m) {
                    $vars[trim($m[1])] = $this->normalizeColor(trim($m[2]));
                }
            }
        }

        $block = $this->extractMediaBlock($css, 'prefers-color-scheme: ' . $scheme);
        if ($block === null) {
            return $vars;
        }

        if (preg_match('/:root\s*\{([^}]*)\}/s', $block, $match) !== 1) {
            return $vars;
        }

        $count = preg_match_all('/(--color-[a-z-]+)\s*:\s*([^;]+);/', $match[1], $matches, PREG_SET_ORDER);
        if (is_int($count) && $count > 0) {
            foreach ($matches as $m) {
                $vars[trim($m[1])] = $this->resolveColor(trim($m[2]), $vars);
            }
        }

        return $vars;
    }

    /**
     * @param array<string, string> $vars
     * @return array<string, array{string, string}>
     */
    private function extractAlerts(string $css, string $scheme, array $vars): array
    {
        $block = $this->extractMediaBlock($css, 'prefers-color-scheme: ' . $scheme);
        if ($block === null) {
            return [];
        }

        $defaultFg = null;
        if (preg_match('/\.alert\s*\{([^}]*)\}/s', $block, $defaultMatch) === 1) {
            if (preg_match('/(?<!-)color\s*:\s*([^;}\n]+)/', $defaultMatch[1], $fgMatch) === 1) {
                $defaultFg = $this->resolveColor(trim($fgMatch[1]), $vars);
            }
        }

        $alerts = [];
        $count = preg_match_all(
            '/\.(alert-(?:info|success|warning|error))\s*\{([^}]*)\}/s',
            $block,
            $matches,
            PREG_SET_ORDER,
        );
        if (is_int($count) && $count > 0) {
            foreach ($matches as $m) {
                $name = $m[1];
                $body = $m[2];
                $bg = null;
                $fg = $defaultFg;
                if (preg_match('/--color\s*:\s*([^;}\n]+)/', $body, $bgMatch) === 1) {
                    $bg = $this->resolveColor(trim($bgMatch[1]), $vars);
                }
                if (preg_match('/(?<!-)color\s*:\s*([^;}\n]+)/', $body, $fgMatch) === 1) {
                    $fg = $this->resolveColor(trim($fgMatch[1]), $vars);
                }
                if ($fg !== null && $bg !== null) {
                    $alerts[$name] = [$fg, $bg];
                }
            }
        }

        return $alerts;
    }

    private function extractMediaBlock(string $css, string $condition): ?string
    {
        $pattern = '/@media\s*\(\s*' . preg_quote($condition, '/') . '\s*\)\s*\{/';
        if (preg_match($pattern, $css, $match, PREG_OFFSET_CAPTURE) !== 1) {
            return null;
        }

        $start = $match[0][1] + strlen($match[0][0]);
        $depth = 1;
        $length = strlen($css);
        $i = $start;
        while ($i < $length && $depth > 0) {
            if ($css[$i] === '{') {
                $depth++;
            } elseif ($css[$i] === '}') {
                $depth--;
            }
            $i++;
        }

        return substr($css, $start, $i - $start - 1);
    }

    /**
     * @param array<string, string> $vars
     */
    private function resolveColor(string $value, array $vars): string
    {
        if (preg_match('/^var\((--[a-z0-9-]+)\)$/', $value, $match) === 1) {
            self::assertArrayHasKey($match[1], $vars, 'Unknown CSS variable ' . $match[1]);

            return $vars[$match[1]];
        }

        return $this->normalizeColor($value);
    }

    private function normalizeColor(string $value): string
    {
        $lower = strtolower(trim($value));

        return self::NAMED_COLORS[$lower] ?? $lower;
    }

    private function contrastRatio(string $hexA, string $hexB): float
    {
        $lumA = $this->relativeLuminance($hexA);
        $lumB = $this->relativeLuminance($hexB);
        $lighter = max($lumA, $lumB);
        $darker = min($lumA, $lumB);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    private function relativeLuminance(string $hex): float
    {
        $clean = ltrim($hex, '#');
        self::assertSame(6, strlen($clean), 'Expected 6-digit hex color, got: ' . $hex);

        $r = $this->linearize(hexdec(substr($clean, 0, 2)) / 255);
        $g = $this->linearize(hexdec(substr($clean, 2, 2)) / 255);
        $b = $this->linearize(hexdec(substr($clean, 4, 2)) / 255);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    private function linearize(float $channel): float
    {
        return $channel <= 0.03928
            ? $channel / 12.92
            : (($channel + 0.055) / 1.055) ** 2.4;
    }
}
