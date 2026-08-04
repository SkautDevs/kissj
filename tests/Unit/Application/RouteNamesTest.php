<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use Tests\AppTestCase;

class RouteNamesTest extends AppTestCase
{
    public function testRouteNamesAreUnique(): void
    {
        $app = $this->getTestApp();

        $names = [];
        foreach ($app->getRouteCollector()->getRoutes() as $route) {
            $name = $route->getName();
            if ($name !== null) {
                $names[] = $name;
            }
        }

        self::assertNotEmpty($names);
        self::assertSame(
            array_values(array_unique($names)),
            $names,
            'duplicate route name - url_for() would silently resolve to the first match',
        );
    }
}
