<?php

declare(strict_types=1);

namespace kissj\FileHandler;

use kissj\Application\ImageUtils;
use kissj\FlashMessages\FlashMessagesInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Psr7\UploadedFile;

readonly class UploadFileHandler
{
    private const array ALLOWED_CONTENT_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
    ];

    public function __construct(
        private FlashMessagesInterface $flashMessages,
        private LoggerInterface $logger,
    ) {
    }

    public function resolveUploadedFile(Request $request, string $slug): ?UploadedFile
    {
        $uploadedFile = $this->getSingleUploadedFile($request, $slug);

        return $uploadedFile === null ? null : $this->checkUploadedFile($uploadedFile, $slug);
    }

    public function resolveUploadedImage(Request $request, string $slug): ?UploadedFile
    {
        $uploadedFile = $this->getSingleUploadedFile($request, $slug);

        return $uploadedFile === null ? null : $this->checkUploadedImage($uploadedFile, $slug);
    }

    /**
     * Each refused file is dropped with its own flash, the others are kept.
     *
     * @return list<UploadedFile>
     */
    public function resolveUploadedImages(Request $request, string $slug): array
    {
        $uploaded = $request->getUploadedFiles()[$slug] ?? [];
        $candidates = $uploaded instanceof UploadedFile ? [$uploaded] : $uploaded;
        if (is_array($candidates) === false) {
            return [];
        }

        $images = [];
        foreach ($candidates as $candidate) {
            if ($candidate instanceof UploadedFile === false) {
                continue;
            }

            $image = $this->checkUploadedImage($candidate, $slug);
            if ($image !== null) {
                $images[] = $image;
            }
        }

        return $images;
    }

    private function getSingleUploadedFile(Request $request, string $slug): ?UploadedFile
    {
        $uploadedFiles = $request->getUploadedFiles();
        if (!array_key_exists($slug, $uploadedFiles) || !$uploadedFiles[$slug] instanceof UploadedFile) {
            // problem - too big file -> not save anything, because always got nulls in request fields
            $this->flashMessages->warning('flash.warning.fileTooBig');

            return null;
        }

        return $uploadedFiles[$slug];
    }

    private function checkUploadedImage(UploadedFile $uploadedFile, string $slug): ?UploadedFile
    {
        if ($this->checkUploadedFile($uploadedFile, $slug) === null) {
            return null;
        }

        $stream = $uploadedFile->getStream();
        $isSupportedImage = ImageUtils::isSupportedRasterImage((string)$stream);
        $stream->rewind();
        if ($isSupportedImage === false) {
            $this->flashMessages->warning('flash.warning.invalidFileType');

            return null;
        }

        return $uploadedFile;
    }

    private function checkUploadedFile(UploadedFile $uploadedFile, string $slug): ?UploadedFile
    {
        $errorNum = $uploadedFile->getError();

        switch ($errorNum) {
            case UPLOAD_ERR_OK:
                // check for too-big files
                $fileSize = $uploadedFile->getSize();
                if ($fileSize === null) {
                    throw new \RuntimeException('Uploaded file size is null.');
                }
                if ($fileSize > 10_000_000) { // 10MB
                    $this->flashMessages->warning('flash.warning.fileTooBig');

                    return null;
                }

                $contentType = $uploadedFile->getClientMediaType();
                if ($contentType === null || !in_array($contentType, self::ALLOWED_CONTENT_TYPES, true)) {
                    $this->flashMessages->warning('flash.warning.invalidFileType');

                    return null;
                }

                return $uploadedFile;
            case UPLOAD_ERR_INI_SIZE:
                $this->flashMessages->warning('flash.warning.fileTooBig');

                return null;
            case UPLOAD_ERR_NO_FILE:
                return null;
            default:
                $this->logger->warning('Unexpected file upload error', ['error' => $errorNum, 'slug' => $slug]);
                $this->flashMessages->warning('flash.warning.fileGeneral');

                return null;
        }
    }
}
