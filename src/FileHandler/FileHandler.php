<?php

namespace kissj\FileHandler;

use kissj\Participant\Participant;
use Slim\Psr7\UploadedFile;

abstract class FileHandler {
    abstract public function getFile(string $filename): File;
    
    abstract public function saveFileTo(Participant $participant, UploadedFile $uploadedFile): Participant;
    
    protected function getNewFilename(): string {
        return \md5(microtime(true));
    }
}
