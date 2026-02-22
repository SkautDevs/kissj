<?php declare(strict_types=1);

namespace Tests\Unit\Event\ContentArbiter;

use kissj\Event\ContentArbiter\ContentArbiterItem;
use kissj\Event\ContentArbiter\ContentArbiterItemType;
use kissj\Event\ContentArbiterIst;
use PHPUnit\Framework\TestCase;

class AbstractContentArbiterTest extends TestCase
{
    public function testGetAllItemsReturns31Items(): void
    {
        $ca = new ContentArbiterIst();
        $items = $ca->getAllItems();

        self::assertCount(31, $items);
        self::assertContainsOnlyInstancesOf(ContentArbiterItem::class, $items);
    }

    public function testGetAllowedItemsFiltersDisabledFields(): void
    {
        $ca = new ContentArbiterIst();
        $allowedItems = $ca->getAllowedItems();
        $allowedIds = array_map(fn (ContentArbiterItem $item) => $item->id, $allowedItems);

        // phone is disabled by default
        self::assertNotContains('telephoneNumber', $allowedIds);
        // firstName is enabled by default
        self::assertContains('firstName', $allowedIds);
    }

    public function testGetAllowedItemsSortedByOrder(): void
    {
        $ca = new ContentArbiterIst();
        $allowedItems = $ca->getAllowedItems();
        $orders = array_map(fn (ContentArbiterItem $item) => $item->order, $allowedItems);

        $sorted = $orders;
        sort($sorted);
        self::assertSame($sorted, $orders);
    }

    public function testDefaultAllowedMatchesOldBoolDefaults(): void
    {
        $ca = new ContentArbiterIst();

        // Fields that were true by default
        self::assertTrue($ca->firstName->allowed);
        self::assertTrue($ca->lastName->allowed);
        self::assertTrue($ca->nickname->allowed);
        self::assertTrue($ca->address->allowed);
        self::assertTrue($ca->gender->allowed);
        self::assertTrue($ca->birthDate->allowed);
        self::assertTrue($ca->health->allowed);
        self::assertTrue($ca->psychicalHealth->allowed);
        self::assertTrue($ca->notes->allowed);

        // Fields that were false by default
        self::assertFalse($ca->contingent->allowed);
        self::assertFalse($ca->patrolName->allowed);
        self::assertFalse($ca->phone->allowed);
        self::assertFalse($ca->country->allowed);
        self::assertFalse($ca->email->allowed);
        self::assertFalse($ca->tshirt->allowed);
        self::assertFalse($ca->uploadFile->allowed);
    }

    public function testEnablingFieldMakesItAppearInAllowed(): void
    {
        $ca = new ContentArbiterIst();
        $ca->phone->allowed = true;
        $allowedIds = array_map(fn (ContentArbiterItem $item) => $item->id, $ca->getAllowedItems());

        self::assertContains('telephoneNumber', $allowedIds);
    }

    public function testPhoneFieldHasCorrectTypeAndPattern(): void
    {
        $ca = new ContentArbiterIst();
        self::assertSame(ContentArbiterItemType::Phone, $ca->phone->type);
        self::assertSame('^\+?[0-9 ]+$', $ca->phone->pattern);
    }

    public function testTshirtFieldIsCompositeType(): void
    {
        $ca = new ContentArbiterIst();
        self::assertSame(ContentArbiterItemType::TshirtComposite, $ca->tshirt->type);
    }
}
