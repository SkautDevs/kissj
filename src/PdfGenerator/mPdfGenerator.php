<?php

declare(strict_types=1);

namespace kissj\PdfGenerator;

use kissj\Application\ImageUtils;
use kissj\Participant\Participant;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Troop\TroopLeader;
use Mpdf\Mpdf;
use Slim\Views\Twig;

class mPdfGenerator extends PdfGenerator
{
    public function __construct(
        private readonly Mpdf $mpdf,
        private readonly Twig $twig,
    ) {
    }

    public function generatePdfReceipt(Participant $participant): string
    {
        $event = $participant->getUserButNotNull()->event;
        $payment = $participant->getFirstPaidPayment();
        $templateData = [
        	'event' => $event,
        	'skautLogo' => ImageUtils::getLocalImageInBase64('/SKAUT_horizontalni_logo_250.png'),
        	'receiptNumber' => $event->eventType->getReceiptNumber($event->slug, $participant, (string)$payment?->id),
        	'eventDates' => $event->startDay->format('j. n. Y') . ' až ' . $event->endDay->format('j. n. Y'),
        	'participant' => $participant,
        	'allOtherParticipants' => $this->getOtherParticipantsIfNeeded($participant),
        	'payment' => $payment,
        	'acceptedDate' => $participant->registrationPayDate?->format('j. n. Y'),
        	'signAndStamp' => ImageUtils::getLocalImageInBase64('/SkautJunakSignStamp.png'),
        ];

        $html = $this->twig->fetch('participant/receipt.twig', $templateData);
        $this->mpdf->WriteHtml($html);

        return $this->mpdf->Output(dest: 'S');
    }

    private function getOtherParticipantsIfNeeded(Participant $participant): ?string
    {
        if ($participant instanceof PatrolLeader) {
            return implode(', ', $this->getParticipantAddresses($participant->patrolParticipants));
        }

        if ($participant instanceof TroopLeader) {
            return implode(', ', $this->getParticipantAddresses($participant->troopParticipants));
        }

        return null;
    }

    /**
     * @param Participant[] $patrolParticipants
     * @return string[]
     */
    private function getParticipantAddresses(array $patrolParticipants): array
    {
        return array_map(fn (Participant $participant) => $participant->getFullName(), $patrolParticipants);
    }
}
