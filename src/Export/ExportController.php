<?php

declare(strict_types=1);

namespace kissj\Export;

use Exception;
use kissj\AbstractController;
use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Participant\ParticipantService;
use kissj\Participant\ParticipantStatisticsService;
use kissj\PdfGenerator\PdfGenerator;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRegistration;
use kissj\Programme\ProgrammeRegistrationRepository;
use kissj\Programme\ProgrammeRepository;
use kissj\User\User;
use League\Csv\ByteSequence;
use League\Csv\EscapeFormula;
use League\Csv\Writer;
use LogicException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Stream;

class ExportController extends AbstractController
{
    private const int BLANK_BADGES_MAX_COUNT = 200;

    public function __construct(
        private readonly ExportService $exportService,
        private readonly ParticipantRepository $participantRepository,
        private readonly ParticipantStatisticsService $participantStatisticsService,
        private readonly PdfGenerator $pdfGenerator,
        private readonly ParticipantService $participantService,
        private readonly ProgrammeRepository $programmeRepository,
        private readonly ProgrammeRegistrationRepository $programmeRegistrationRepository,
    ) {
    }

    public function exportHealthData(
        Response $response,
        User $user,
        Event $event
    ): Response {
        $csvRows = $this->exportService->healthDataToCSV($event, $user);
        $this->logger->info('Exported health data about participants', ['userId' => $user->id]);

        return $this->outputCSVresponse($response, $csvRows, $event->slug . '_health');
    }

    public function exportPaidData(
        Response $response,
        User $user,
        Event $event
    ): Response {
        $csvRows = $this->exportService->paidContactDataToCSV($event, $user);
        $this->logger->info('Exported data about participants which paid', ['userId' => $user->id]);

        return $this->outputCSVresponse($response, $csvRows, $event->slug . '_paid');
    }

    public function exportFullData(
        Response $response,
        User $user,
        Event $event
    ): Response {
        $csvRows = $this->exportService->allRegistrationDataToCSV($event, $user);
        $this->logger->info('Exported FULL current data about participants', ['userId' => $user->id]);

        return $this->outputCSVresponse($response, $csvRows, $event->slug . '_full');
    }

    public function exportProgrammes(Response $response, Event $event): Response
    {
        $csvRows = $this->exportService->programmeRegistrationsToCSV($event);

        return $this->outputCSVresponse($response, $csvRows, $event->slug . '_programmes');
    }

    public function exportFoodSummary(
        Response $response,
        User $user,
        Event $event,
    ): Response {
        $csvRows = $this->participantStatisticsService
            ->createParticipantFoodPlanFromEvent($event, false)
            ->aggregatedToCSV();
        $this->logger->info('Exported participants food summary', ['userId' => $user->id]);

        return $this->outputCSVresponse($response, $csvRows, $event->slug . '_food');
    }

    public function exportFoodPatrolsAndTroops(
        Response $response,
        User $user,
        Event $event,
    ): Response {
        $csvRows = $this->participantStatisticsService->createParticipantFoodPlanFromEvent(
            $event,
            true,
            [
                ParticipantRole::PatrolLeader,
                ParticipantRole::PatrolParticipant,
                ParticipantRole::TroopLeader,
                ParticipantRole::TroopParticipant,
                ParticipantRole::TechnicalTeamLeader,
                ParticipantRole::TechnicalTeamParticipant,
            ],
        )->aggregatedToCSV();
        $this->logger->info('Exported patrols and troops food summary', ['userId' => $user->id]);

        return $this->outputCSVresponse($response, $csvRows, $event->slug . '_patrol_troop_food');
    }

    /**
     * @param array<array<string>> $csvRows
     */
    private function outputCSVresponse(
        Response $response,
        array $csvRows,
        string $fileName,
    ): Response {
        $fileName .= '_' . DateTimeUtils::getDateTime()->format(DATE_ATOM);

        $response = $response->withAddedHeader('Content-Type', 'text/csv');
        $response = $response->withAddedHeader('Content-Disposition', 'attachment; filename="' . $fileName . '.csv";');
        $response = $response->withAddedHeader('Expires', '0');
        $response = $response->withAddedHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response = $response->withAddedHeader('Pragma', 'no-cache');

        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->setDelimiter(',');
        $csv->setOutputBOM(ByteSequence::BOM_UTF8);
        $csv->addFormatter(new CsvCellFlattener());
        $csv->addFormatter(new EscapeFormula());
        $csv->insertAll($csvRows);

        $body = $response->getBody();
        $body->write($csv->toString());

        return $response->withBody($body);
    }

    public function exportPatrolsRoster(
        Request $request,
        Response $response,
        Event $event,
    ): Response {
        if ($event->allowPatrols === false) {
            $this->flashMessages->error('flash.error.patrolsNotAllowed');
            $this->sentryCollector->collect(new Exception('Patrols are not allowed, but user tried to export roster'));

            return $this->redirect($request, $response, 'dashboard');
        }

        $patrolsRoster = $this->participantService->getPatrolsRoster($event);

        return $this->streamPdf($response, $this->pdfGenerator->generatePatrolRoster(
            $event,
            $patrolsRoster,
            $event->eventType->getRosterTemplateName(),
        ));
    }

    public function showBadgesForm(Response $response, Event $event): Response
    {
        return $this->view->render($response, 'admin/badges-admin.twig', [
            'event' => $event,
            'availableRoles' => $event->getAvailableRoles(),
        ]);
    }

    public function exportBadges(Request $request, Response $response, Event $event, User $user): Response
    {
        $allowedRoles = $event->getAvailableRoles();
        $rawRoles = $request->getQueryParams()['roles'] ?? [];
        if (!is_array($rawRoles)) {
            $rawRoles = [];
        }

        $selectedRoles = [];
        foreach ($rawRoles as $rawRole) {
            if (!is_string($rawRole)) {
                continue;
            }

            $role = ParticipantRole::tryFrom($rawRole);
            if ($role instanceof ParticipantRole && in_array($role, $allowedRoles, true)) {
                $selectedRoles[] = $role;
            }
        }

        if ($selectedRoles === []) {
            $this->flashMessages->warning('flash.warning.noRolesSelected');

            return $this->redirect($request, $response, 'admin-badges-form', ['eventSlug' => $event->slug]);
        }

        $participants = $this->participantRepository->getParticipantsForBadges($event, $selectedRoles);
        $this->logger->info('Generated badges', ['eventSlug' => $event->slug, 'userId' => $user->id]);

        return $this->streamPdf($response, $this->pdfGenerator->generateBadges($event, $participants));
    }

    public function exportBlankBadges(Request $request, Response $response, Event $event): Response
    {
        $rawCount = $request->getQueryParams()['count'] ?? 0;
        $count = is_numeric($rawCount) ? (int)$rawCount : 0;
        $count = max(1, min($count, self::BLANK_BADGES_MAX_COUNT));

        return $this->streamPdf($response, $this->pdfGenerator->generateBlankBadges($event, $count));
    }

    public function exportProgrammesPrint(Response $response, Event $event): Response
    {
        $sheets = $this->buildAttendanceSheets($event, null);

        return $this->streamPdf($response, $this->pdfGenerator->generateProgrammeAttendance($event, $sheets));
    }

    public function printProgramme(Response $response, Event $event, int $programmeId): Response
    {
        $this->programmeRepository->getActiveById($programmeId, $event);
        $sheets = $this->buildAttendanceSheets($event, $programmeId);

        return $this->streamPdf($response, $this->pdfGenerator->generateProgrammeAttendance($event, $sheets));
    }

    /**
     * @return list<array{programme: Programme, registrations: list<ProgrammeRegistration>}>
     */
    private function buildAttendanceSheets(Event $event, ?int $onlyProgrammeId): array
    {
        $registrationsByProgramme = $this->programmeRegistrationRepository
            ->findActiveForEventGroupedByProgramme($event);
        $sheets = [];
        foreach ($this->programmeRepository->findActiveForEvent($event) as $programme) {
            if ($programme->isPreregistered) {
                continue;
            }
            if ($onlyProgrammeId !== null && $programme->id !== $onlyProgrammeId) {
                continue;
            }
            $sheets[] = [
                'programme' => $programme,
                'registrations' => $registrationsByProgramme[$programme->id] ?? [],
            ];
        }

        return $sheets;
    }

    private function streamPdf(Response $response, string $pdf): Response
    {
        $stream = fopen('php://temp', 'rb+');
        if ($stream === false) {
            $exception = new LogicException('Cannot access temp stream for PDF export');
            $this->sentryCollector->collect($exception);

            throw $exception;
        }

        fwrite($stream, $pdf);
        rewind($stream);

        return $response->withHeader('Content-Type', 'application/pdf')->withBody(new Stream($stream));
    }
}
