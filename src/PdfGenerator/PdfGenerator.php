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
}
