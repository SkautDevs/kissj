<?php

declare(strict_types=1);

namespace Tests\Unit\Phpstan;

use kissj\Phpstan\UserStatusWriteOnlyInUserServiceRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<UserStatusWriteOnlyInUserServiceRule>
 */
class UserStatusWriteOnlyInUserServiceRuleTest extends RuleTestCase
{
    protected function getRule(): UserStatusWriteOnlyInUserServiceRule
    {
        return new UserStatusWriteOnlyInUserServiceRule();
    }

    public function testReportsStatusWriteOutsideUserServiceAndTestsOnly(): void
    {
        $this->analyse([__DIR__ . '/data/user-status-write.php'], [
            [
                'Change User::$status only through UserService (setUserOpen/Closed/Approved/Paid/Cancelled), so status '
                    . 'transitions stay in one place.',
                11,
            ],
        ]);
    }
}
