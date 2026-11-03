<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Sfm;

use kissj\Event\ContentArbiter\ContentArbiterItem;
use kissj\Event\ContentArbiter\ContentArbiterItemType;
use kissj\Event\ContentArbiterGuest;
use kissj\Event\EventType\EventTypeDefault;

class EventTypeSfm extends EventTypeDefault
{
    #[\Override]
    public function getContentArbiterGuest(): ContentArbiterGuest
    {
        $ca = parent::getContentArbiterGuest();
        $ca->unit->allowed = true;
        $ca->unit->placeholder = null;
        $ca->skills->allowed = true;
        $ca->skills->placeholder = null;
        $ca->skills->order = 155;
        $ca->notes->placeholder = null;

        $ca->preferredPosition->allowed = true;
        $ca->preferredPosition->required = false;
        $ca->preferredPosition->order = 156;
        $ca->preferredPosition->helpText = null;
        $ca->preferredPosition->options = ContentArbiterItem::selfMappedOptions([
            'detail.sfmTraining.patrolLeader',
            'detail.sfmTraining.candidate',
            'detail.sfmTraining.leader',
            'detail.sfmTraining.instructor',
            'detail.sfmTraining.unitMinimum',
        ]);
        $ca->birthPlace->allowed = true;
        $ca->birthPlace->required = false;
        $ca->birthPlace->order = 157;
        $ca->birthPlace->placeholder = null;
        $ca->languages->allowed = true;
        $ca->languages->required = false;
        $ca->languages->order = 158;
        $ca->languages->placeholder = null;
        $ca->languages->type = ContentArbiterItemType::Textarea;
        $ca->languages->helpText = 'detail.sfmEvents-helptext';
        $ca->food->allowed = true;
        $ca->food->options = ContentArbiterItem::selfMappedOptions($this->getFoodOptions());

        $this->askForTopic($ca->swimming, 291, $this->getGroupOneTopics());
        $ca->swimming->helpText = 'detail.sfmTopic.group1-helptext';
        $this->askForTopic($ca->scarf, 292, $this->getGroupTwoTopics());
        $ca->scarf->helpText = 'detail.sfmTopic.group2-helptext';
        $this->askForTopic($ca->driver, 293, $this->getGroupTwoTopics());

        return $ca;
    }

    /**
     * @param array<string, string> $topics
     */
    private function askForTopic(ContentArbiterItem $item, int $order, array $topics): void
    {
        $item->allowed = true;
        $item->placeholder = null;
        $item->order = $order;
        $item->options = ['' => 'detail.sfmTopic.choose'] + $topics;
    }

    /**
     * @return array<string, string>
     */
    private function getGroupOneTopics(): array
    {
        return ContentArbiterItem::selfMappedOptions([
            'detail.sfmTopic.education',
            'detail.sfmTopic.delegates',
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function getGroupTwoTopics(): array
    {
        return ContentArbiterItem::selfMappedOptions([
            'detail.sfmTopic.rovering',
            'detail.sfmTopic.youth',
            'detail.sfmTopic.environment',
        ]);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getTranslationFilePaths(): array
    {
        return [
            'cs' => __DIR__ . '/cs_sfm.yaml',
        ];
    }

    #[\Override]
    public function getScriptNameWithoutLeadingSlash(): string
    {
        return 'sfm/sfm.js';
    }
}
