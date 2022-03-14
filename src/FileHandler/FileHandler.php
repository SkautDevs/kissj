<?php

namespace kissj\FileHandler;

use kissj\Participant\Participant;
use Slim\Psr7\UploadedFile;

abstract class FileHandler {
    abstract public function getFile(string $filename): File;
    
    public function saveFileTo(Participant $participant, UploadedFile $uploadedFile): Participant
    {
        $newFilename = $this->getNewFilename();
        $this->saveFile($uploadedFile, $newFilename);

        $participant->uploadedFilename = $newFilename;
        $participant->uploadedOriginalFilename = $uploadedFile->getClientFilename();
        $participant->uploadedContenttype = $uploadedFile->getClientMediaType();

        return $participant;
    }
    
    abstract public function saveFile(UploadedFile $uploadedFile, string $newFilename): UploadedFile;
    
    protected function getNewFilename(): string {
        return \md5((string)microtime(true));
    }
}
