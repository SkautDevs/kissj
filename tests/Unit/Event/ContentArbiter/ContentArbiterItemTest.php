<?php declare(strict_types=1);

namespace Tests\Unit\Event\ContentArbiter;

use kissj\Event\ContentArbiter\ContentArbiterItem;
use kissj\Event\ContentArbiter\ContentArbiterItemType;
use PHPUnit\Framework\TestCase;

class ContentArbiterItemTest extends TestCase
{
    public function testConstructionWithDefaults(): void
    {
        $item = new ContentArbiterItem(
            id: 'telephoneNumber',
            allowed: false,
            type: ContentArbiterItemType::Phone,
            order: 140,
            label: 'detail.phone',
            placeholder: 'detail.phonePlaceholder',
        );

        self::assertSame('telephoneNumber', $item->id);
        self::assertFalse($item->allowed);
        self::assertSame(ContentArbiterItemType::Phone, $item->type);
        self::assertSame(140, $item->order);
        self::assertSame('detail.phone', $item->label);
        self::assertSame('detail.phonePlaceholder', $item->placeholder);
        self::assertTrue($item->required);
        self::assertNull($item->defaultValue);
        self::assertNull($item->pattern);
        self::assertSame([], $item->options);
        self::assertSame([], $item->extraClasses);
    }

    public function testConstructionWithAllParams(): void
    {
        $item = new ContentArbiterItem(
            id: 'foodPreferences',
            allowed: true,
            type: ContentArbiterItemType::Select,
            order: 190,
            label: 'detail.food',
            placeholder: '',
            required: false,
            defaultValue: 'detail.foodWithout',
            pattern: null,
            options: ['detail.foodWithout', 'detail.foodVegetarian'],
            extraClasses: ['food-select'],
        );

        self::assertTrue($item->allowed);
        self::assertFalse($item->required);
        self::assertSame('detail.foodWithout', $item->defaultValue);
        self::assertSame(['detail.foodWithout', 'detail.foodVegetarian'], $item->options);
        self::assertSame(['food-select'], $item->extraClasses);
    }

    public function testPropertiesAreMutable(): void
    {
        $item = new ContentArbiterItem(
            id: 'telephoneNumber',
            allowed: false,
            type: ContentArbiterItemType::Phone,
            order: 140,
            label: 'detail.phone',
            placeholder: 'detail.phonePlaceholder',
        );

        $item->allowed = true;
        $item->required = false;
        $item->label = 'custom.phone';
        $item->options = ['opt1', 'opt2'];

        self::assertTrue($item->allowed);
        self::assertFalse($item->required);
        self::assertSame('custom.phone', $item->label);
        self::assertSame(['opt1', 'opt2'], $item->options);
    }
}
