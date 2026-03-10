<?php

declare(strict_types=1);

namespace kissj\FileHandler;

use kissj\Participant\Participant;
use Slim\Psr7\UploadedFile;

abstract class SaveFileHandler
{
    abstract public function getFile(string $filename): File;

    public function saveFileTo(
        Participant $participant,
        UploadedFile $uploadedFile,
        string $fileItemId,
    ): Participant {
        $newFilename = $this->getNewFilename();
        $this->saveFile($uploadedFile, $newFilename);

        $participant->setUploadedFile(
            $fileItemId,
            $newFilename,
            $uploadedFile->getClientFilename(),
            $uploadedFile->getClientMediaType(),
        );

        return $participant;
    }

    abstract public function saveFile(UploadedFile $uploadedFile, string $newFilename): UploadedFile;

    protected function getNewFilename(): string
    {
        return \bin2hex(\random_bytes(16));
    }
}
