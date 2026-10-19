<?php

declare(strict_types=1);

namespace Tests\Unit\Phpstan;

use kissj\Phpstan\StaticLogMessageRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<StaticLogMessageRule>
 */
class StaticLogMessageRuleTest extends RuleTestCase
{
    private const string MESSAGE = 'Log message must be a static string, put variables into the context array.';

    protected function getRule(): StaticLogMessageRule
    {
        return new StaticLogMessageRule();
    }

    public function testReportsNonStaticLogMessagesInAppCode(): void
    {
        $this->analyse([__DIR__ . '/data/static-log-message.php'], [
            [self::MESSAGE, 25],
            [self::MESSAGE, 26],
            [self::MESSAGE, 27],
            [self::MESSAGE, 28],
            [self::MESSAGE, 29],
            [self::MESSAGE, 30],
            [self::MESSAGE, 31],
            [self::MESSAGE, 32],
            [self::MESSAGE, 33],
            [self::MESSAGE, 43],
            [self::MESSAGE, 44],
            [self::MESSAGE, 46],
            [self::MESSAGE, 57],
        ]);
    }
}
