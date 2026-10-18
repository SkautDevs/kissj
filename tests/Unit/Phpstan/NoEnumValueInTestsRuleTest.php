<?php

declare(strict_types=1);

namespace Tests\Unit\Phpstan;

use kissj\Phpstan\NoEnumValueInTestsRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<NoEnumValueInTestsRule>
 */
class NoEnumValueInTestsRuleTest extends RuleTestCase
{
    private const string MESSAGE = 'Use the literal backing value in tests instead of Enum::Case->value, so a changed enum value fails the test.';

    protected function getRule(): NoEnumValueInTestsRule
    {
        return new NoEnumValueInTestsRule();
    }

    public function testReportsEnumCaseValueInTestCodeOnly(): void
    {
        $this->analyse([__DIR__ . '/data/enum-value.php'], [
            [self::MESSAGE, 19],
            [self::MESSAGE, 33],
        ]);
    }

    public function testAllowsOwnEnumInsideItsTestClassOnly(): void
    {
        $this->analyse([__DIR__ . '/data/GenderTest.php'], [
            [self::MESSAGE, 19],
        ]);
    }
}
