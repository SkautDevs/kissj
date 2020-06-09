<?php

namespace kissj\Export;

use kissj\AbstractController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ExportController extends AbstractController {
    private $exportService;

    public function __construct(ExportService $exportService) {
        $this->exportService = $exportService;
    }

    public function exportHealthData(Request $request, Response $response) {
        /** @var \kissj\Event\Event $event */
        $event = $request->getAttribute('user')->event;
        $csvRows = $this->exportService->healthDataToCSV($event);
        $this->logger->info('Exported health data about participants');

        return $this->exportService->outputCSVresponse($response, $csvRows, $event->slug.'_health');
    }

    public function exportPaidData(Request $request, Response $response) {
        /** @var \kissj\Event\Event $event */
        $event = $request->getAttribute('user')->event;
        $csvRows = $this->exportService->paidContactDataToCSV($event);
        $this->logger->info('Exported data about participants which paid');

        return $this->exportService->outputCSVresponse($response, $csvRows, $event->slug.'_paid');
    }

    public function exportFullData(Request $request, Response $response) {
        /** @var \kissj\Event\Event $event */
        $event = $request->getAttribute('user')->event;
        $csvRows = $this->exportService->allRegistrationDataToCSV($event);
        $this->logger->info('Exported FULL current data about participants');

        return $this->exportService->outputCSVresponse($response, $csvRows, $event->slug.'_full');
    }
}
