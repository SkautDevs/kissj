<?php

declare(strict_types=1);

namespace Fixture\Phpstan {
    use DateTimeImmutable;

    function datetimeConstructor(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    function clockFunctions(object $holder): void
    {
        $sql = 'date(participant.arrival_date)';
        $holder->date();
        $holder->time();
        microtime(true);
        hrtime();

        time();
        date('Y');
        \date('Y');
        gmdate('Y');
        strtotime('now');
        mktime(0, 0, 0, 1, 1, 2026);
        date_create();
        date_create_immutable();
        \DATE('Y');
        getdate();
        localtime();
        idate('Y');
        gmmktime(0, 0, 0, 1, 1, 2026);
        strftime('%Y');
    }
}

namespace Tests\Unit {
    use DateTimeImmutable;

    function clockFunctionsInTests(): DateTimeImmutable
    {
        time();
        date('Y');

        return new DateTimeImmutable();
    }
}

namespace Tests {
    function clockFunctionsInBareTestsNamespace(): int
    {
        return time();
    }
}

namespace kissj\Application {
    use DateTimeImmutable;

    class DateTimeUtils
    {
        public static function getDateTime(): DateTimeImmutable
        {
            time();

            return new DateTimeImmutable();
        }
    }
}
