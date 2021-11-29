<?php

namespace kissj\FileHandler;

use GuzzleHttp\Psr7\LazyOpenStream;
use Slim\Psr7\UploadedFile;

class LocalFileHandler extends FileHandler {
    public function __construct(private string $uploadFolder = __DIR__.'/../../uploads/')
    {
    }

    public function getFile(string $filename): File {
        return new File(
            new LazyOpenStream($this->uploadFolder.$filename, 'r'),
            mime_content_type($this->uploadFolder.$filename)
        );
    }

    public function saveFile(UploadedFile $uploadedFile, string $newFilename): UploadedFile {
        $uploadedFile->moveTo($this->uploadFolder.$newFilename);

        return $uploadedFile;
    }
}
