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
    private const string MESSAGE = 'Access the database through a Repository class (src/Orm/Repository.php), never via Connection/Fluent directly.';

    protected function getRule(): DatabaseAccessOnlyInRepositoryRule
    {
        return new DatabaseAccessOnlyInRepositoryRule();
    }

    public function testReportsConnectionAndFluentUseOutsideRepositoriesOnly(): void
    {
        $this->analyse([__DIR__ . '/data/database-access.php'], [
            [self::MESSAGE, 11],
            [self::MESSAGE, 12],
            [self::MESSAGE, 13],
            [self::MESSAGE, 14],
            [self::MESSAGE, 15],
        ]);
    }
}
