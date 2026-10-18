<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use LogicException;
use Tests\AppTestCase;
use Tests\Support\ProjectFiles;

class RouteNamesTest extends AppTestCase
{
    private const array TWIG_PATTERNS = [
        '/url_for\(\s*\'([^\']+)\'/',
    ];

    private const array PHP_PATTERNS = [
        '/->redirect\(\s*\$\w+,\s*\$\w+,\s*\'([^\']+)\'/',
        '/createRedirectResponse\(\s*\$\w+,\s*\'([^\']+)\'/',
        '/->urlFor\(\s*\'([^\']+)\'/',
        '/->fullUrlFor\(\s*[^,]+,\s*\'([^\']+)\'/',
        '/function getFailureRedirectRouteName\(\): string\s*\{\s*return \'([^\']+)\'/',
    ];

    public function testRouteNamesAreUnique(): void
    {
        $names = $this->routeNames();

        self::assertNotEmpty($names);
        self::assertSame(
            array_values(array_unique($names)),
            $names,
            'duplicate route name - url_for() would silently resolve to the first match',
        );
    }

    public function testEveryReferencedRouteNameExists(): void
    {
        $routeNames = $this->routeNames();
        $references = [
            ...$this->collectReferences(ProjectFiles::withExtension('src/Templates', 'twig'), self::TWIG_PATTERNS),
            ...$this->collectReferences(ProjectFiles::withExtension('src', 'php'), self::PHP_PATTERNS),
        ];

        $unknown = [];
        foreach ($references as [$path, $routeName]) {
            if (in_array($routeName, $routeNames, true) === false) {
                $unknown[] = $path . ': ' . $routeName;
            }
        }

        self::assertSame([], $unknown, 'route name referenced but no route is registered under it');
    }

    /**
     * @return list<string>
     */
    private function routeNames(): array
    {
        $names = [];
        foreach ($this->getTestApp()->getRouteCollector()->getRoutes() as $route) {
            $name = $route->getName();
            if ($name !== null) {
                $names[] = $name;
            }
        }

        return $names;
    }

    /**
     * @param list<string> $paths
     * @param list<string> $patterns
     * @return list<array{string, string}>
     */
    private function collectReferences(array $paths, array $patterns): array
    {
        $references = [];
        foreach ($patterns as $pattern) {
            $matchCount = 0;
            foreach ($paths as $path) {
                $content = file_get_contents($path);
                if ($content === false) {
                    throw new LogicException('Cannot read ' . $path);
                }

                preg_match_all($pattern, $content, $matches);
                foreach ($matches[1] as $routeName) {
                    $references[] = [$path, $routeName];
                    $matchCount++;
                }
            }

            self::assertGreaterThan(0, $matchCount, 'pattern ' . $pattern . ' matched nothing: the call shape it guards has changed');
        }

        return $references;
    }
}
