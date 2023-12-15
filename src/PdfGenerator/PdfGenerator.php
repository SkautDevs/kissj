<?php

declare(strict_types=1);

namespace kissj\PdfGenerator;

use kissj\Participant\Participant;

abstract class PdfGenerator
{
    abstract public function generatePdfReceipt(Participant $participant): string;
}
