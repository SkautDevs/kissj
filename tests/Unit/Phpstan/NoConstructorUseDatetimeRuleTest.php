<?php

declare(strict_types=1);

namespace Tests\Unit\Phpstan;

use kissj\Phpstan\NoConstructorUseDatetimeRule;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<NoConstructorUseDatetimeRule>
 */
class NoConstructorUseDatetimeRuleTest extends RuleTestCase
{
    private const string CONSTRUCTOR_MESSAGE = 'Do not use constructor of DateTimeInterface() directly, use '
        . 'DateTimeUtils::getDateTime() instead.';

    protected function getRule(): NoConstructorUseDatetimeRule
    {
        return new NoConstructorUseDatetimeRule(self::getContainer()->getByType(ReflectionProvider::class));
    }

    public function testReportsDateTimeConstructorAndClockFunctionsOnlyOutsideDateTimeUtilsAndClockFreeTests(): void
    {
        $this->analyse([__DIR__ . '/data/datetime.php'], [
            [self::CONSTRUCTOR_MESSAGE, 10],
            [$this->clockMessage('time'), 21],
            [$this->clockMessage('date'), 22],
            [$this->clockMessage('date'), 23],
            [$this->clockMessage('gmdate'), 24],
            [$this->clockMessage('strtotime'), 25],
            [$this->clockMessage('mktime'), 26],
            [$this->clockMessage('date_create'), 27],
            [$this->clockMessage('date_create_immutable'), 28],
            [$this->clockMessage('date'), 29],
            [$this->clockMessage('getdate'), 30],
            [$this->clockMessage('localtime'), 31],
            [$this->clockMessage('idate'), 32],
            [$this->clockMessage('gmmktime'), 33],
            [$this->clockMessage('strftime'), 34],
            [self::CONSTRUCTOR_MESSAGE, 46],
        ]);
    }

    private function clockMessage(string $function): string
    {
        return sprintf('Do not use %s() for dates and times, use DateTimeUtils::getDateTime() instead.', $function);
    }
}
