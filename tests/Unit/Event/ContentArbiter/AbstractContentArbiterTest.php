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

    public function testLabelsMatchTemplateTranslationKeys(): void
    {
        $ca = new ContentArbiterIst();

        self::assertSame('detail.firstName', $ca->firstName->label);
        self::assertSame('detail.scoutNick', $ca->nickname->label);
        self::assertSame('detail.issues', $ca->health->label);
        self::assertSame('detail.psychicalIssues', $ca->psychicalHealth->label);
        self::assertSame('detail.foodHeader', $ca->food->label);
        self::assertSame('detail.swimSkill', $ca->swimming->label);
        self::assertSame('detail.notice', $ca->notes->label);
        self::assertSame('detail.language', $ca->languages->label);
    }

    public function testPlaceholdersMatchTemplateTranslationKeys(): void
    {
        $ca = new ContentArbiterIst();

        self::assertSame('detail.firstNamePlaceholder', $ca->firstName->placeholder);
        self::assertSame('detail.scoutNickPlaceholder', $ca->nickname->placeholder);
        self::assertSame('detail.issues-placeholder', $ca->health->placeholder);
        self::assertSame('detail.medicaments-placeholder', $ca->medicaments->placeholder);
        self::assertSame('detail.psychicalIssues-placeholder', $ca->psychicalHealth->placeholder);
        self::assertSame('detail.emergencyContact-placeholder', $ca->emergencyContact->placeholder);
        self::assertSame('detail.language-placeholder', $ca->languages->placeholder);
        self::assertSame('detail.skills-placeholder', $ca->skills->placeholder);
        self::assertSame('detail.notice-placeholder', $ca->notes->placeholder);
        self::assertSame('detail.idNumber-placeholder', $ca->idNumber->placeholder);
    }

    public function testFieldTypesMatchTemplateRendering(): void
    {
        $ca = new ContentArbiterIst();

        self::assertSame(ContentArbiterItemType::Text, $ca->address->type);
        self::assertSame(ContentArbiterItemType::Text, $ca->health->type);
        self::assertSame(ContentArbiterItemType::Text, $ca->medicaments->type);
        self::assertSame(ContentArbiterItemType::Text, $ca->psychicalHealth->type);
        self::assertSame(ContentArbiterItemType::Select, $ca->driver->type);
        self::assertSame(ContentArbiterItemType::Select, $ca->scarf->type);
        self::assertSame(ContentArbiterItemType::Textarea, $ca->notes->type);
    }

    public function testSelectOptionsMatchTemplateValues(): void
    {
        $ca = new ContentArbiterIst();

        self::assertSame(['man', 'woman', 'other'], $ca->gender->options);
        self::assertSame(
            ['detail.swimSkillNo', 'detail.swimSkillLess50', 'detail.swimSkillMore50'],
            $ca->swimming->options,
        );
        self::assertSame(['dont', 'less 10000 km', 'more 10000 km'], $ca->driver->options);
        self::assertSame(['yes', 'no'], $ca->scarf->options);
    }
}
