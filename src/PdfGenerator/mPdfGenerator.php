<?php

declare(strict_types=1);

namespace kissj\PdfGenerator;

use kissj\Application\ImageUtils;
use kissj\Event\Event;
use kissj\Participant\Participant;
use kissj\Payment\Payment;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolsRoster;
use kissj\Participant\Troop\TroopLeader;
use kissj\Telemetry\MetricName;
use kissj\Telemetry\Metrics;
use Mpdf\Mpdf;
use Slim\Views\Twig;

class mPdfGenerator extends PdfGenerator
{
    public function __construct(
        private readonly Mpdf $mpdf,
        private readonly Twig $twig,
        private readonly Metrics $metrics,
    ) {
        $this->mpdf->shrink_tables_to_fit = 1;
    }

    public function generatePdfReceipt(Participant $participant, string $templateName): string
    {
        $event = $participant->getUserButNotNull()->event;
        $payments = $participant->getAllPaidPayment();

        $receipts = array_map(fn (Payment $payment) => [
            'payment' => $payment,
            'receiptNumber' => $event->eventType->getReceiptNumber($event->slug, $participant, (string)$payment->id),
            'acceptedDate' => $payment->paidAt?->format('j. n. Y'),
        ], $payments);

        $templateData = [
            'event' => $event,
            'skautLogo' => ImageUtils::getLocalImageInBase64($event->eventType->getSkautLogoPath($participant)),
            'eventDates' => $event->startDay->format('j. n. Y') . ' až ' . $event->endDay->format('j. n. Y'),
            'participant' => $participant,
            'allOtherParticipants' => $this->getOtherParticipantsIfNeeded($participant),
            'receipts' => $receipts,
            'signAndStamp' => ImageUtils::getLocalImageInBase64($event->eventType->getSkautStampSignPath($participant)),
        ];

        $start = microtime(true);
        $outcome = 'failed';
        try {
            $html = $this->twig->fetch($templateName, $templateData);
            $this->mpdf->WriteHTML($html);
            /** @var string $output */
            $output = $this->mpdf->Output(dest: 'S');
            $outcome = 'success';
        } finally {
            $durationMs = (microtime(true) - $start) * 1000.0;
            $this->metrics->count(
                MetricName::PdfsGenerated,
                1,
                ['type' => 'receipt', 'outcome' => $outcome],
            );
            $this->metrics->distributionMs(
                MetricName::PdfsGenerationTime,
                $durationMs,
                ['type' => 'receipt', 'outcome' => $outcome],
            );
        }

        return $output;
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

        $start = microtime(true);
        $outcome = 'failed';
        try {
            $html = $this->twig->fetch($templateName, $templateData);
            $this->mpdf->WriteHTML($html);
            /** @var string $output */
            $output = $this->mpdf->Output(dest: 'S');
            $outcome = 'success';
        } finally {
            $durationMs = (microtime(true) - $start) * 1000.0;
            $this->metrics->count(
                MetricName::PdfsGenerated,
                1,
                ['type' => 'roster', 'outcome' => $outcome],
            );
            $this->metrics->distributionMs(
                MetricName::PdfsGenerationTime,
                $durationMs,
                ['type' => 'roster', 'outcome' => $outcome],
            );
        }

        return $output;
    }
}
