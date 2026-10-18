<?php

declare(strict_types=1);

namespace Fixture\Phpstan {
    use LeanMapper\Connection;
    use LeanMapper\Fluent;

    function accessDatabase(Connection $connection, Fluent $fluent, ?Connection $nullableConnection, ?Fluent $nullableFluent): void
    {
        $connection->query('SELECT 1');
        $fluent->where('id = %i', 1);
        $nullableConnection?->query('SELECT 1');
        $nullableFluent?->where('id = %i', 1);
        new Connection(['driver' => 'sqlite3', 'database' => ':memory:']);
    }
}

namespace Tests\Unit\Phpstan\data {
    use LeanMapper\Connection;

    function accessDatabaseInTest(Connection $connection): void
    {
        $connection->query('SELECT 1');
    }
}

namespace Tests {
    use LeanMapper\Connection;

    function accessDatabaseInBareTestsNamespace(Connection $connection): void
    {
        $connection->query('SELECT 1');
    }
}
