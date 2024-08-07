<?php

declare(strict_types=1);

namespace kissj\FileHandler;

use kissj\FlashMessages\FlashMessagesInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\UploadedFile;

readonly class UploadFileHandler
{
    public function __construct(
        private FlashMessagesInterface $flashMessages,
    ) {
    }

    public function resolveUploadedFile(Request $request): ?UploadedFile
    {
        $uploadedFiles = $request->getUploadedFiles();
        if (!array_key_exists('uploadFile', $uploadedFiles) || !$uploadedFiles['uploadFile'] instanceof UploadedFile) {
            // problem - too big file -> not save anything, because always got nulls in request fields
            $this->flashMessages->warning('flash.warning.fileTooBig');

            return null;
        }

        $errorNum = $uploadedFiles['uploadFile']->getError();

        switch ($errorNum) {
            case UPLOAD_ERR_OK:
                $uploadedFile = $uploadedFiles['uploadFile'];

                // check for too-big files
                $fileSize = $uploadedFile->getSize();
                if ($fileSize === null) {
                    throw new \RuntimeException('Uploaded file size is null.');
                }
                if ($fileSize > 10_000_000) { // 10MB
                    $this->flashMessages->warning('flash.warning.fileTooBig');

                    return null;
                }

                return $uploadedFile;
            case UPLOAD_ERR_INI_SIZE:
                $this->flashMessages->warning('flash.warning.fileTooBig');

                return null;
            default:
                return null;
        }
    }

}
