<?php

declare(strict_types=1);

namespace kissj\FileHandler;

use GuzzleHttp\Psr7\LazyOpenStream;
use RuntimeException;
use Slim\Psr7\UploadedFile;

class LocalSaveFileHandler extends SaveFileHandler
{
    public function __construct(private readonly string $uploadFolder = __DIR__ . '/../../uploads/')
    {
    }

    public function getFile(string $filename): File
    {
        if (file_exists($this->uploadFolder . $filename) === false) {
            throw new RuntimeException('File not found: ' . $filename);
        }

        $mimeContentType = mime_content_type($this->uploadFolder . $filename);
        if ($mimeContentType === false) {
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

    public function saveContents(string $contents, string $contentType): string
    {
        $newFilename = $this->getNewFilename();
        if (file_put_contents($this->uploadFolder . $newFilename, $contents) === false) {
            throw new RuntimeException('Cannot write file: ' . $newFilename);
        }

        return $newFilename;
    }

    /**
     * A file that is already gone counts as deleted, so a concurrent delete raises no warning.
     */
    public function deleteFile(string $filename): void
    {
        $path = $this->uploadFolder . $filename;
        if (!@unlink($path) && file_exists($path)) {
            throw new RuntimeException('Cannot delete file: ' . $filename);
        }
    }
}
