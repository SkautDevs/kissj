<?php

namespace kissj\Export;

use kissj\AbstractController;
use Psr\Http\Message\ResponseInterface as Response;

class ExportController extends AbstractController {
    private $exportService;

    public function __construct(ExportService $exportService) {
        $this->exportService = $exportService;
    }

    public function exportPaidData(Response $response) {
        $eventName = 'AQUA2020';
        $csvRows = $this->exportService->paidContactDataToCSV($eventName);
        $this->logger->info('Downloaded current emails about paid participants');

        return $this->exportService->outputCSVresponse($response, $csvRows, $eventName.'_paid');
    }

    public function exportFullData(Response $response) {
        // TODO make event aware
        $eventName = 'AQUA2020';
        $csvRows = $this->exportService->allRegistrationDataToCSV($eventName);
        $this->logger->info('Downloaded FULL current data about participants');

        return $this->exportService->outputCSVresponse($response, $csvRows, $eventName.'_full');
    }
}
