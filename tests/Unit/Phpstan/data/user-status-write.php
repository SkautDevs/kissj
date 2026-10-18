<?php

declare(strict_types=1);

namespace Fixture\Phpstan {
    use kissj\User\User;
    use kissj\User\UserStatus;

    function writeUserStatus(User $user): void
    {
        $user->status = UserStatus::Paid;
        $user->email = 'unrelated@example.com';
    }
}

namespace Tests\Unit\Phpstan\data {
    use kissj\User\User;
    use kissj\User\UserStatus;

    function writeUserStatusInTest(User $user): void
    {
        $user->status = UserStatus::Paid;
    }
}

namespace Tests {
    use kissj\User\User;
    use kissj\User\UserStatus;

    function writeUserStatusInBareTestsNamespace(User $user): void
    {
        $user->status = UserStatus::Paid;
    }
}
