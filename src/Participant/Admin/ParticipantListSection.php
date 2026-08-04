<?php

declare(strict_types=1);

namespace kissj\Participant\Admin;

use kissj\Event\AbstractContentArbiter;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRole;

readonly class ParticipantListSection
{
    /** @param Participant[] $participants */
    public function __construct(
        public ParticipantRole $role,
        public string $titleKey,
        public array $participants,
        public AbstractContentArbiter $contentArbiter,
        public ?AbstractContentArbiter $childContentArbiter = null,
        public ?string $emptyTitleKey = null,
    ) {
    }
}
