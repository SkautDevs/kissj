<?php

namespace kissj\FileHandler;

use GuzzleHttp\Psr7\LazyOpenStream;
use kissj\Participant\Participant;
use Slim\Psr7\UploadedFile;

class LocalFileHandler extends FileHandler {
    private string $uploadFolder;

    public function __construct(string $uploadFolder = __DIR__.'/../../../uploads/') {
        $this->uploadFolder = $uploadFolder;
    }

    public function getFile(string $filename): File {
        return new File(
            new LazyOpenStream($this->uploadFolder.$filename, 'r'),
            mime_content_type($this->uploadFolder.$filename)
        );
    }

    public function saveFileTo(Participant $participant, UploadedFile $uploadedFile): Participant {
        $newFilename = $this->getNewFilename();
        $uploadedFile->moveTo($this->uploadFolder.$newFilename);

        $participant->uploadedFilename = $newFilename;
        $participant->uploadedOriginalFilename = $uploadedFile->getClientFilename();
        $participant->uploadedContenttype = $uploadedFile->getClientMediaType();

        return $participant;
    }
}
