<?php

declare(strict_types=1);

namespace kissj\PdfGenerator;

use kissj\Application\ImageUtils;
use kissj\Event\Event;
use kissj\Participant\Participant;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolsRoster;
use kissj\Participant\Troop\TroopLeader;
use Mpdf\Mpdf;
use Slim\Views\Twig;

class mPdfGenerator extends PdfGenerator
{
    public function __construct(
        private readonly Mpdf $mpdf,
        private readonly Twig $twig,
    ) {
        $this->mpdf->shrink_tables_to_fit = 1;
    }

    public function generatePdfReceipt(Participant $participant, string $templateName): string
    {
        $event = $participant->getUserButNotNull()->event;
        $payments = $participant->getAllPaidPayment();
        $firstPaymentId = $payments[0]->id ?? null;

        $templateData = [
        	'event' => $event,
        	'skautLogo' => ImageUtils::getLocalImageInBase64($event->eventType->getSkautLogoPath($participant)),
        	'receiptNumber' => $event->eventType->getReceiptNumber($event->slug, $participant, (string)$firstPaymentId),
        	'eventDates' => $event->startDay->format('j. n. Y') . ' až ' . $event->endDay->format('j. n. Y'),
        	'participant' => $participant,
        	'allOtherParticipants' => $this->getOtherParticipantsIfNeeded($participant),
        	'payments' => $payments,
        	'acceptedDate' => $participant->registrationPayDate?->format('j. n. Y'),
            'signAndStamp' => ImageUtils::getLocalImageInBase64($event->eventType->getSkautStampSignPath($participant)),
        ];

        $html = $this->twig->fetch($templateName, $templateData);
        $this->mpdf->WriteHTML($html);

        return $this->mpdf->Output(dest: 'S');
    }

    private function getOtherParticipantsIfNeeded(Participant $participant): ?string
    {
        if ($participant instanceof PatrolLeader) {
            return implode(', ', $this->getParticipantNames($participant->patrolParticipants));
        }

        if ($participant instanceof TroopLeader) {
            return implode(', ', $this->getParticipantNames($participant->troopParticipants));
        }

        return null;
    }

    /**
     * @param Participant[] $patrolParticipants
     * @return string[]
     */
    private function getParticipantNames(array $patrolParticipants): array
    {
        return array_map(fn (Participant $participant) => $participant->getFullName(), $patrolParticipants);
    }

    public function generatePatrolRoster(
        Event $event,
        PatrolsRoster $patrolsRoster,
        string $templateName
    ): string {
        $templateData = [
            'event' => $event,
            'patrolsRoster' => $patrolsRoster,
        ];

        $html = $this->twig->fetch($templateName, $templateData);
        $htmlLength = strlen($html);

        $targetChunkSize = 999_999;
        $offset = 0;

        while ($offset < $htmlLength) {
            $chunk = substr($html, $offset, $targetChunkSize);
            $chunkLength = strlen($chunk);

            if ($offset + $chunkLength < $htmlLength) {
                // find the last closing tag in this chunk
                $lastTagPos = strrpos($chunk, '>');

                if ($lastTagPos !== false) {
                    // adjust chunk to end at a tag boundary, so split won't emerge inside a HTML tag
                    $chunk = substr($chunk, 0, $lastTagPos + 1);
                    $chunkLength = strlen($chunk);
                }
            }

            $this->mpdf->WriteHTML($chunk);
            $offset += $chunkLength;
        }

        return $this->mpdf->Output(dest: 'S');
    }
}
