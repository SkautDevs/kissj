<?php

namespace kissj\Export;

use kissj\AbstractController;
use Slim\Http\Response;

class ExportController extends AbstractController {
    private $exportService;

    public function __construct(ExportService $exportService) {
        $this->exportService = $exportService;
    }

    public function exportFullData(Response $response) {
        // TODO make event aware
        $eventName = 'AQUA2020';
        $csvRows = $this->exportService->allRegistrationDataToCSV($eventName);
        $this->logger->info('Downloaded full current data about participants');

        return $this->exportService->outputCSVresponse($response, $csvRows, $eventName.'_full');
    }
}
