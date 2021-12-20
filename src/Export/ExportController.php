<?php declare(strict_types=1);

namespace kissj\Export;

use kissj\AbstractController;
use kissj\Event\Event;
use kissj\User\User;
use League\Csv\ByteSequence;
use League\Csv\Writer;
use Psr\Http\Message\ResponseInterface as Response;

class ExportController extends AbstractController
{
    public function __construct(
        private ExportService $exportService,
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
     * @param Response             $response
     * @param array<array<string>> $csvRows
     * @param string               $fileName
     * @param bool                 $addTimestamp
     * @return Response
     */
    private function outputCSVresponse(
        Response $response,
        array $csvRows,
        string $fileName,
        bool $addTimestamp = true
    ): Response {
        if ($addTimestamp) {
            $fileName .= '_' . date(DATE_ATOM);
        }

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
}
