<?php

declare(strict_types=1);

namespace kissj\PdfGenerator;

use kissj\Event\Event;
use kissj\Participant\Participant;
use kissj\Participant\Patrol\PatrolsRoster;

abstract class PdfGenerator
{
    abstract public function generatePdfReceipt(
        Participant $participant,
        string $templateName,
    ): string;

    abstract public function generatePatrolRoster(
        Event $event,
        PatrolsRoster $patrolsRoster,
        string $templateName,
    ): string;

    /**
     * @param Participant[] $participants
     */
    abstract public function buildBadgesHtml(Event $event, array $participants): string;

    /**
     * @param Participant[] $participants
     */
    abstract public function generateBadges(Event $event, array $participants): string;

    abstract public function buildBlankBadgesHtml(Event $event, int $count): string;

    abstract public function generateBlankBadges(Event $event, int $count): string;
}
