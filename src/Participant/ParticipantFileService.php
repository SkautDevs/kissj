<?php

declare(strict_types=1);

namespace kissj\Participant;

use kissj\Event\ContentArbiter\ContentArbiterItem;
use kissj\Event\ContentArbiter\ContentArbiterItemType;
use kissj\FileHandler\SaveFileHandler;
use kissj\FileHandler\UploadFileHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\UploadedFile;

readonly class ParticipantFileService
{
    public function __construct(
        private SaveFileHandler $saveFileHandler,
        private UploadFileHandler $uploadFileHandler,
    ) {
    }

    /**
     * @param list<ContentArbiterItem> $items
     */
    public function handleUploadedFiles(Participant $participant, Request $request, array $items): void
    {
        foreach ($items as $item) {
            if ($item->type !== ContentArbiterItemType::File) {
                continue;
            }

            $uploadedFile = $this->uploadFileHandler->resolveUploadedFile($request, $item->slug);
            if ($uploadedFile instanceof UploadedFile) {
                $this->saveFileHandler->saveFileTo($participant, $uploadedFile, $item->slug);
            }
        }
    }
}
