<?php

declare(strict_types=1);

namespace kissj\Export;

use Exception;
use kissj\AbstractController;
use kissj\Event\Event;
use kissj\Participant\ParticipantRepository;
use kissj\PdfGenerator\PdfGenerator;
use kissj\User\User;
use League\Csv\ByteSequence;
use League\Csv\Writer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Stream;

class ExportController extends AbstractController
{
    public function __construct(
        private readonly ExportService $exportService,
        private readonly ParticipantRepository $participantRepository,
        private readonly PdfGenerator $pdfGenerator,
    ) {
    }

    public function exportHealthData(
        Response $response,
        User $user,
        Event $event
    ): Response {
        $csvRows = $this->exportService->healthDataToCSV($event, $user);
        $this->logger->info('Exported health data about participants by user with ID' . $user->id);

        return $this->outputCSVresponse($response, $csvRows, $event->slug . '_health');
    }

    public function exportPaidData(
        Response $response,
        User $user,
        Event $event
    ): Response {
        $csvRows = $this->exportService->paidContactDataToCSV($event, $user);
        $this->logger->info('Exported data about participants which paid by user with ID' . $user->id);

        return $this->outputCSVresponse($response, $csvRows, $event->slug . '_paid');
    }

    public function exportFullData(
        Response $response,
        User $user,
        Event $event
    ): Response {
        $csvRows = $this->exportService->allRegistrationDataToCSV($event, $user);
        $this->logger->info('Exported FULL current data about participants by user with ID' . $user->id);

        return $this->outputCSVresponse($response, $csvRows, $event->slug . '_full');
    }

    /**
     * @param array<array<string>> $csvRows
     */
    private function outputCSVresponse(
        Response $response,
        array $csvRows,
        string $fileName,
    ): Response {
        $fileName .= '_' . date(DATE_ATOM);

        $response = $response->withAddedHeader('Content-Type', 'text/csv');
        $response = $response->withAddedHeader('Content-Disposition', 'attachment; filename="' . $fileName . '.csv";');
        $response = $response->withAddedHeader('Expires', '0');
        $response = $response->withAddedHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response = $response->withAddedHeader('Pragma', 'no-cache');

        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->setDelimiter(',');
        $csv->setOutputBOM(ByteSequence::BOM_UTF8);
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
            $this->flashMessages->error($this->translator->trans('flash.error.patrolsNotAllowed'));
            $this->sentryCollector->collect(new Exception('Patrols are not allowed, but user tried to export roster'));

            return $this->redirect($request, $response, 'dashboard');
        }

        $stream = fopen('php://temp', 'rb+');
        if ($stream === false) {
            $this->flashMessages->error($this->translator->trans('flash.error.cannotAccessTemp'));
            $this->sentryCollector->collect(new Exception('Cannot access temp file'));

            return $this->redirect($request, $response, 'dashboard');
        }

        $patrolsRoster = $this->participantRepository->getPatrolsRoster($event);

        fwrite($stream, $this->pdfGenerator->generatePatrolRoster(
            $event,
            $patrolsRoster,
            $event->eventType->getRosterTemplateName(),
        ));
        rewind($stream);

        return $response->withHeader('Content-Type', 'application/pdf')->withBody(new Stream($stream));
    }
}
