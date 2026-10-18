<?php

declare(strict_types=1);

namespace Fixture\Phpstan {
    function readSuperglobals(string $local): array
    {
        return [
            $_GET['page'] ?? null,
            $_POST['name'] ?? null,
            $_SESSION['user'] ?? null,
            $_ENV['DEBUG'] ?? null,
            $local,
        ];
    }
}

namespace Tests\Unit\Phpstan\data {
    function setSessionInTest(int $userId): void
    {
        $_SESSION['user'] = ['id' => $userId];
    }
}

namespace Tests {
    function setSessionInBareTestsNamespace(int $userId): void
    {
        $_SESSION['user'] = ['id' => $userId];
    }
}
