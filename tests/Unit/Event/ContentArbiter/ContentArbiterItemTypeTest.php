<?php declare(strict_types=1);

namespace Tests\Unit\Event\ContentArbiter;

use kissj\Event\ContentArbiter\ContentArbiterItemType;
use PHPUnit\Framework\TestCase;

class ContentArbiterItemTypeTest extends TestCase
{
    public function testAllExpectedTypesExist(): void
    {
        $expectedTypes = [
            'text', 'email', 'phone', 'date', 'select',
            'textarea', 'file', 'checkbox', 'tshirtComposite',
        ];

        $actualValues = array_map(fn (ContentArbiterItemType $t) => $t->value, ContentArbiterItemType::cases());
        sort($expectedTypes);
        sort($actualValues);

        self::assertSame($expectedTypes, $actualValues);
    }
}
