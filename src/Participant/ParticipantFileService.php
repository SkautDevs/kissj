<?php

declare(strict_types=1);

namespace kissj\Participant;

use kissj\Event\ContentArbiter\ContentArbiterItemType;
use kissj\FileHandler\SaveFileHandler;
use kissj\FileHandler\UploadFileHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\UploadedFile;

readonly class ParticipantFileService
{
    public function __construct(
        private ParticipantService $participantService,
        private SaveFileHandler $saveFileHandler,
        private UploadFileHandler $uploadFileHandler,
    ) {
    }

    public function handleUploadedFiles(Participant $participant, Request $request): void
    {
        $contentArbiter = $this->participantService->getContentArbiterForParticipant($participant);

        foreach ($contentArbiter->getAllowedItems() as $item) {
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
