<?php

declare(strict_types=1);

namespace Fixture\Phpstan {
    use kissj\Participant\Gender;

    function enumCaseValueInProduction(): string
    {
        return Gender::Man->value;
    }
}

namespace Tests\Unit\Phpstan\data {
    use kissj\Participant\Gender;

    function enumCaseValue(): string
    {
        return Gender::Man->value;
    }

    function dynamicEnumValue(Gender $gender): string
    {
        return $gender->value;
    }
}

namespace Tests {
    use kissj\Participant\Gender;

    function enumCaseValueInBareTestsNamespace(): string
    {
        return Gender::Man->value;
    }
}
