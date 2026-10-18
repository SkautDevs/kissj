<?php

declare(strict_types=1);

namespace Tests\Unit\Phpstan;

use kissj\Phpstan\NoConstructorUseDatetimeRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<NoConstructorUseDatetimeRule>
 */
class NoConstructorUseDatetimeRuleTest extends RuleTestCase
{
    protected function getRule(): NoConstructorUseDatetimeRule
    {
        return new NoConstructorUseDatetimeRule();
    }

    public function testReportsDateTimeConstructorOutsideDateTimeUtils(): void
    {
        $this->analyse([__DIR__ . '/data/datetime-constructor.php'], [
            ['Do not use constructor of DateTimeInterface() directly, use DateTimeUtils::getDateTime() instead.', 11],
        ]);
    }
}
