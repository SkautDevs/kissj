<?php

declare(strict_types=1);

namespace Tests\Unit\Phpstan;

use kissj\Phpstan\DatabaseAccessOnlyInRepositoryRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<DatabaseAccessOnlyInRepositoryRule>
 */
class DatabaseAccessOnlyInRepositoryRuleTest extends RuleTestCase
{
    private const string MESSAGE = 'Access the database through a Repository class (src/Orm/Repository.php), never via '
        . 'Connection/Fluent directly.';

    protected function getRule(): DatabaseAccessOnlyInRepositoryRule
    {
        return new DatabaseAccessOnlyInRepositoryRule();
    }

    public function testReportsConnectionAndFluentUseOutsideRepositoriesOnly(): void
    {
        $this->analyse([__DIR__ . '/data/database-access.php'], [
            [self::MESSAGE, 15],
            [self::MESSAGE, 16],
            [self::MESSAGE, 17],
            [self::MESSAGE, 18],
            [self::MESSAGE, 19],
        ]);
    }
}
