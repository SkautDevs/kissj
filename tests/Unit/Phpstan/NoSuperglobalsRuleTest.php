<?php

declare(strict_types=1);

namespace Tests\Unit\Phpstan;

use kissj\Phpstan\NoSuperglobalsRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<NoSuperglobalsRule>
 */
class NoSuperglobalsRuleTest extends RuleTestCase
{
    private const string MESSAGE = 'Read request/session/env data through the PSR-7 request, EnvSettings, '
        . 'FlashMessagesBySession or UserRegeneration, not superglobals.';

    protected function getRule(): NoSuperglobalsRule
    {
        return new NoSuperglobalsRule();
    }

    public function testReportsSuperglobalsOutsideAllowedPlaces(): void
    {
        $this->analyse([__DIR__ . '/data/superglobals.php'], [
            [self::MESSAGE, 9],
            [self::MESSAGE, 10],
            [self::MESSAGE, 11],
            [self::MESSAGE, 12],
        ]);
    }

    public function testAllowsPhinxConfiguration(): void
    {
        $this->analyse([__DIR__ . '/data/phinxConfiguration.php'], []);
    }
}
