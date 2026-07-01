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
use kissj\Payment\QrCodeService;
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
        private readonly QrCodeService $qrCodeService,
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

        return $this->writeHtmlToPdf($templateName, $templateData, 'receipt');
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

        return $this->writeHtmlToPdf($templateName, $templateData, 'roster');
    }

    /**
     * @param Participant[] $participants
     * @return array<string, mixed>
     */
    private function buildBadgesTemplateData(Event $event, array $participants): array
    {
        $badges = array_map(function (Participant $participant) use ($event): array {
            return [
                'participant' => $participant,
                'ca' => $event->eventType->getContentArbiterForRole($participant->getRoleOrFail()),
                'qr' => $this->qrCodeService->generateQrBase64FromString(
                    $participant->getQrParticipantInfoString(),
                ),
            ];
        }, array_values($participants));

        return [
            'event' => $event,
            'badges' => $badges,
            'badgeCss' => $this->getBadgeFullCss($event),
        ];
    }

    /**
     * @param Participant[] $participants
     */
    public function buildBadgesHtml(Event $event, array $participants): string
    {
        return $this->twig->fetch(
            $event->eventType->getBadgeTemplateName(),
            $this->buildBadgesTemplateData($event, $participants),
        );
    }

    /**
     * @param Participant[] $participants
     */
    public function generateBadges(Event $event, array $participants): string
    {
        return $this->writeHtmlToPdf(
            $event->eventType->getBadgeTemplateName(),
            $this->buildBadgesTemplateData($event, $participants),
            'badge',
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildBlankBadgesTemplateData(Event $event, int $count): array
    {
        return [
            'event' => $event,
            'count' => range(1, max(1, $count)),
            'badgeCss' => $this->getBadgeFullCss($event),
        ];
    }

    public function buildBlankBadgesHtml(Event $event, int $count): string
    {
        return $this->twig->fetch(
            $event->eventType->getBlankBadgeTemplateName(),
            $this->buildBlankBadgesTemplateData($event, $count),
        );
    }

    private function getBadgeFullCss(Event $event): string
    {
        $css = file_get_contents(__DIR__ . '/../../public/badge.css');
        $css = $css === false ? '' : $css;

        $override = $event->eventType->getBadgeStylesheetNameWithoutLeadingSlash();
        if ($override !== null) {
            $overrideCss = file_get_contents(__DIR__ . '/../../public/' . $override);
            if ($overrideCss !== false) {
                $css .= "\n" . $overrideCss;
            }
        }

        return $css;
    }

    public function generateBlankBadges(Event $event, int $count): string
    {
        return $this->writeHtmlToPdf(
            $event->eventType->getBlankBadgeTemplateName(),
            $this->buildBlankBadgesTemplateData($event, $count),
            'badge_blank',
        );
    }

    /**
     * @param array<string, mixed> $templateData rendered inside the timed block so render failures are counted too
     */
    private function writeHtmlToPdf(string $templateName, array $templateData, string $metricType): string
    {
        $start = microtime(true);
        $outcome = 'failed';
        try {
            $this->mpdf->WriteHTML($this->twig->fetch($templateName, $templateData));
            /** @var string $output */
            $output = $this->mpdf->Output(dest: 'S');
            $outcome = 'success';
        } finally {
            $durationMs = (microtime(true) - $start) * 1000.0;
            $this->metrics->count(
                MetricName::PdfsGenerated,
                1,
                ['type' => $metricType, 'outcome' => $outcome],
            );
            $this->metrics->distributionMs(
                MetricName::PdfsGenerationTime,
                $durationMs,
                ['type' => $metricType, 'outcome' => $outcome],
            );
        }

        return $output;
    }
}
