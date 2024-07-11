<?php

declare(strict_types=1);

namespace kissj\FileHandler;

use GuzzleHttp\Psr7\LazyOpenStream;
use Slim\Psr7\UploadedFile;

class LocalSaveFileHandler extends SaveFileHandler
{
    public function __construct(private readonly string $uploadFolder = __DIR__ . '/../../uploads/')
    {
    }

    public function getFile(string $filename): File
    {
        $mimeContentType = mime_content_type($this->uploadFolder . $filename);
        if ($mimeContentType === false) {
            /** @phpstan-ignore shipmonk.variableTypeOverwritten */
            $mimeContentType = 'unknown_mime_type';
        }

        return new File(
            new LazyOpenStream($this->uploadFolder . $filename, 'r'),
            $mimeContentType,
        );
    }

    public function saveFile(UploadedFile $uploadedFile, string $newFilename): UploadedFile
    {
        $uploadedFile->moveTo($this->uploadFolder . $newFilename);

        return $uploadedFile;
    }
}
