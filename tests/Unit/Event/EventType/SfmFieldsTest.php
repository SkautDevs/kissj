<?php

declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use Closure;
use kissj\Event\ContentArbiter\ContentArbiterItem;
use kissj\Event\ContentArbiter\ContentArbiterItemType;
use kissj\Event\ContentArbiterGuest;
use kissj\Event\EventType\Sfm\EventTypeSfm;
use kissj\Translation\TranslatorFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-type ItemGetter Closure(ContentArbiterGuest): ContentArbiterItem
 */
class SfmFieldsTest extends TestCase
{
    /**
     * @return array<string, array{ItemGetter, bool}>
     */
    public static function guestQuestionProvider(): array
    {
        return [
            'unit' => [fn (ContentArbiterGuest $ca): ContentArbiterItem => $ca->unit, true],
            'skills' => [fn (ContentArbiterGuest $ca): ContentArbiterItem => $ca->skills, true],
            'notes' => [fn (ContentArbiterGuest $ca): ContentArbiterItem => $ca->notes, false],
        ];
    }

    /**
     * @param ItemGetter $getItem
     */
    #[DataProvider('guestQuestionProvider')]
    public function testGuestQuestionIsAskedWithoutPlaceholder(Closure $getItem, bool $required): void
    {
        $item = $getItem((new EventTypeSfm())->getContentArbiterGuest());

        self::assertTrue($item->allowed);
        self::assertSame($required, $item->required);
        self::assertNull($item->placeholder);
        self::assertFalse($item->editableAfterLock);
    }

    /**
     * @return array<string, array{ItemGetter, int, array<string, string>, ?string}>
     */
    public static function topicQuestionProvider(): array
    {
        $groupTwo = [
            '' => 'detail.sfmTopic.choose',
            'detail.sfmTopic.rovering' => 'detail.sfmTopic.rovering',
            'detail.sfmTopic.youth' => 'detail.sfmTopic.youth',
            'detail.sfmTopic.environment' => 'detail.sfmTopic.environment',
        ];

        return [
            'swimming' => [
                fn (ContentArbiterGuest $ca): ContentArbiterItem => $ca->swimming,
                291,
                [
                    '' => 'detail.sfmTopic.choose',
                    'detail.sfmTopic.education' => 'detail.sfmTopic.education',
                    'detail.sfmTopic.delegates' => 'detail.sfmTopic.delegates',
                ],
                'detail.sfmTopic.group1-helptext',
            ],
            'scarf' => [
                fn (ContentArbiterGuest $ca): ContentArbiterItem => $ca->scarf,
                292,
                $groupTwo,
                'detail.sfmTopic.group2-helptext',
            ],
            'driver' => [fn (ContentArbiterGuest $ca): ContentArbiterItem => $ca->driver, 293, $groupTwo, null],
        ];
    }

    /**
     * @param ItemGetter $getItem
     * @param array<string, string> $options
     */
    #[DataProvider('topicQuestionProvider')]
    public function testTopicQuestionIsRequiredSelectOfTopics(
        Closure $getItem,
        int $order,
        array $options,
        ?string $helpText,
    ): void {
        $item = $getItem((new EventTypeSfm())->getContentArbiterGuest());

        self::assertTrue($item->allowed);
        self::assertTrue($item->required);
        self::assertNull($item->placeholder);
        self::assertSame($order, $item->order);
        self::assertSame($options, $item->options);
        self::assertSame($helpText, $item->helpText);
    }

    public function testSkillsQuestionIsOrderedBeforeTopics(): void
    {
        self::assertSame(155, (new EventTypeSfm())->getContentArbiterGuest()->skills->order);
    }

    public function testTrainingQuestionIsOptionalCheckboxList(): void
    {
        $item = (new EventTypeSfm())->getContentArbiterGuest()->preferredPosition;

        self::assertTrue($item->allowed);
        self::assertFalse($item->required);
        self::assertSame(156, $item->order);
        self::assertNull($item->helpText);
        self::assertFalse($item->editableAfterLock);
        self::assertSame([
            'detail.sfmTraining.patrolLeader' => 'detail.sfmTraining.patrolLeader',
            'detail.sfmTraining.candidate' => 'detail.sfmTraining.candidate',
            'detail.sfmTraining.leader' => 'detail.sfmTraining.leader',
            'detail.sfmTraining.instructor' => 'detail.sfmTraining.instructor',
            'detail.sfmTraining.unitMinimum' => 'detail.sfmTraining.unitMinimum',
        ], $item->options);
    }

    public function testOtherTrainingQuestionIsOptionalText(): void
    {
        $item = (new EventTypeSfm())->getContentArbiterGuest()->birthPlace;

        self::assertTrue($item->allowed);
        self::assertFalse($item->required);
        self::assertSame(157, $item->order);
        self::assertNull($item->placeholder);
        self::assertSame(ContentArbiterItemType::Text, $item->type);
    }

    public function testEventsQuestionIsOptionalTextareaWithHelpText(): void
    {
        $item = (new EventTypeSfm())->getContentArbiterGuest()->languages;

        self::assertTrue($item->allowed);
        self::assertFalse($item->required);
        self::assertSame(158, $item->order);
        self::assertNull($item->placeholder);
        self::assertSame(ContentArbiterItemType::Textarea, $item->type);
        self::assertSame('detail.sfmEvents-helptext', $item->helpText);
    }

    public function testMedicamentsStayUnasked(): void
    {
        self::assertFalse((new EventTypeSfm())->getContentArbiterGuest()->medicaments->allowed);
    }

    public function testFoodIsStandardRequiredSelect(): void
    {
        $item = (new EventTypeSfm())->getContentArbiterGuest()->food;

        self::assertTrue($item->allowed);
        self::assertTrue($item->required);
        self::assertSame([
            'detail.foodWithout' => 'detail.foodWithout',
            'detail.foodVegetarian' => 'detail.foodVegetarian',
            'detail.foodLactoseFree' => 'detail.foodLactoseFree',
            'detail.foodGlutenFree' => 'detail.foodGlutenFree',
            'detail.foodOther' => 'detail.foodOther',
        ], $item->options);
    }

    public function testScriptSyncsTopicSelects(): void
    {
        $scriptName = (new EventTypeSfm())->getScriptNameWithoutLeadingSlash();

        self::assertSame('sfm/sfm.js', $scriptName);
        self::assertFileExists(__DIR__ . '/../../../../public/' . $scriptName);
    }

    public function testCzechTranslationsNameTheTopics(): void
    {
        $eventType = new EventTypeSfm();
        $translator = (new TranslatorFactory('cs', null, false))->createForEventType($eventType);
        $keys = [
            'detail.swimSkill',
            'detail.scarf',
            'detail.driver',
            'detail.sfmTopic.choose',
            'detail.sfmTopic.education',
            'detail.sfmTopic.delegates',
            'detail.sfmTopic.rovering',
            'detail.sfmTopic.youth',
            'detail.sfmTopic.environment',
            'detail.sfmTopic.group1-helptext',
            'detail.sfmTopic.group2-helptext',
        ];

        foreach ($keys as $key) {
            self::assertNotSame($key, $translator->trans($key));
        }
    }

    public function testCzechTranslationsAskTheQuestions(): void
    {
        $eventType = new EventTypeSfm();
        $translator = (new TranslatorFactory('cs', null, false))->createForEventType($eventType);

        self::assertSame(['cs'], array_keys($eventType->getTranslationFilePaths()));
        self::assertSame('Ve kterém středisku aktuálně aktivně působíš?', $translator->trans('detail.unit'));
        self::assertSame(
            'Co teď děláš ve svém roverském kmeni/oddíle/středisku?',
            $translator->trans('detail.skills'),
        );
        self::assertSame('Je něco, co by ses nás chtěl nebo chtěla zeptat?', $translator->trans('detail.notice'));
        self::assertSame(
            'Jaké skautské vzdělávání jsi zatím absolvoval nebo absolvovala?',
            $translator->trans('detail.position-title'),
        );
        self::assertSame('rádcovské', $translator->trans('detail.sfmTraining.patrolLeader'));
        self::assertSame('čekatelské', $translator->trans('detail.sfmTraining.candidate'));
        self::assertSame('vůdcovské', $translator->trans('detail.sfmTraining.leader'));
        self::assertSame('instruktorské', $translator->trans('detail.sfmTraining.instructor'));
        self::assertSame('kurz střediskového minima', $translator->trans('detail.sfmTraining.unitMinimum'));
        self::assertSame('Jiné skautské vzdělávání', $translator->trans('detail.birthPlace'));
        self::assertSame(
            'Jakých celostátních nebo velkých akcí ses zúčastnil nebo zúčastnila v posledních třech letech'
            . ' (2024, 2025, 2026)?',
            $translator->trans('detail.language'),
        );
        self::assertSame('jiné - prosím upřesni v poslední otázce', $translator->trans('detail.foodOther'));
        self::assertSame(
            'v případě jiné diety, která není uvedena výše, uveď svou preferenci do poslední otázky',
            $translator->trans('detail.food-helptext'),
        );
        $eventsHelp = $translator->trans('detail.sfmEvents-helptext');
        self::assertStringContainsString('Celostátní kolo Svojsíkova závodu', $eventsHelp);
        self::assertStringContainsString('Helpdesk', $eventsHelp);
        self::assertStringContainsString('víckrát', $eventsHelp);
    }
}
