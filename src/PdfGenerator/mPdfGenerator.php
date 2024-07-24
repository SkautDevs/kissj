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
        $payment = $participant->getFirstPaidPayment();
        $templateData = [
        	'event' => $event,
        	'skautLogo' => ImageUtils::getLocalImageInBase64($event->eventType->getSkautLogoPath($participant)),
        	'receiptNumber' => $event->eventType->getReceiptNumber($event->slug, $participant, (string)$payment?->id),
        	'eventDates' => $event->startDay->format('j. n. Y') . ' až ' . $event->endDay->format('j. n. Y'),
        	'participant' => $participant,
        	'allOtherParticipants' => $this->getOtherParticipantsIfNeeded($participant),
        	'payment' => $payment,
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
        $this->mpdf->WriteHTML($html);

        return $this->mpdf->Output(dest: 'S');
    }
}
