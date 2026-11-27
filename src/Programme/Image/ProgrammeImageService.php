<?php

declare(strict_types=1);

namespace kissj\Programme\Image;

use kissj\FileHandler\File;
use kissj\FileHandler\SaveFileHandler;
use kissj\Programme\Programme;
use LogicException;
use Psr\Log\LoggerInterface;
use Slim\Psr7\UploadedFile;
use Throwable;

readonly class ProgrammeImageService
{
    public function __construct(
        private ProgrammeImageRepository $programmeImageRepository,
        private ProgrammeImageProcessor $programmeImageProcessor,
        private SaveFileHandler $saveFileHandler,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Stores the image resized, with its thumbnail, after the programme's other images.
     *
     * @return ProgrammeImageUploadRefusal|null null when the image was stored
     */
    public function addUpload(Programme $programme, UploadedFile $file): ?ProgrammeImageUploadRefusal
    {
        $bytes = (string)$file->getStream();
        // checked before decoding, which would exhaust the memory on a huge bitmap
        if ($this->programmeImageProcessor->exceedsPixelCap($bytes)) {
            return ProgrammeImageUploadRefusal::TooManyPixels;
        }

        try {
            $processed = $this->programmeImageProcessor->process($bytes);
        } catch (UndecodableImageException) {
            return ProgrammeImageUploadRefusal::Undecodable;
        }

        $image = new ProgrammeImage();
        $image->programme = $programme;
        $image->filename = $this->saveFileHandler->saveContents($processed->full->bytes, $processed->full->contentType);
        $image->thumbFilename = $this->saveFileHandler->saveContents(
            $processed->thumb->bytes,
            $processed->thumb->contentType,
        );
        $image->alt = '';
        $image->sortOrder = $this->programmeImageRepository->getNextSortOrder($programme);
        $this->programmeImageRepository->persist($image);

        return null;
    }

    public function updateAlt(ProgrammeImage $image, string $alt): void
    {
        $image->alt = $alt;
        $this->programmeImageRepository->persist($image);
    }

    /**
     * The full file stays while another image still references it: images imported from the former
     * single image column can share one file.
     */
    public function delete(ProgrammeImage $image): void
    {
        $filename = $image->filename;
        $thumbFilename = $image->thumbFilename;
        $this->programmeImageRepository->delete($image);

        if ($thumbFilename !== null) {
            $this->deleteStoredFile($thumbFilename);
        }
        if ($this->programmeImageRepository->countByFilename($filename) === 0) {
            $this->deleteStoredFile($filename);
        }
    }

    public function move(ProgrammeImage $image, string $direction): void
    {
        $images = $this->programmeImageRepository->findForProgramme($image->programme);
        $position = $this->positionOf($images, $image);
        $neighbourPosition = $direction === 'up' ? $position - 1 : $position + 1;
        if (isset($images[$neighbourPosition]) === false) {
            return;
        }

        $images[$position] = $images[$neighbourPosition];
        $images[$neighbourPosition] = $image;
        $this->programmeImageRepository->transactional(function () use ($images): void {
            foreach ($images as $index => $orderedImage) {
                if ($orderedImage->sortOrder === $index) {
                    continue;
                }

                $orderedImage->sortOrder = $index;
                $this->programmeImageRepository->persist($orderedImage);
            }
        });
    }

    public function openFull(ProgrammeImage $image): ?File
    {
        return $this->open($image, $image->filename);
    }

    /**
     * Images from before thumbnails existed get theirs on the first request.
     */
    public function openThumb(ProgrammeImage $image): ?File
    {
        if ($image->thumbFilename !== null) {
            return $this->open($image, $image->thumbFilename);
        }

        $original = $this->read($image, $image->filename);
        if ($original === null || $this->programmeImageProcessor->exceedsPixelCap($original)) {
            return null;
        }

        try {
            $thumb = $this->programmeImageProcessor->thumbnail($original);
        } catch (UndecodableImageException) {
            $this->logUnreadable($image);

            return null;
        }

        $thumbFilename = $this->saveFileHandler->saveContents($thumb->bytes, $thumb->contentType);
        if ($this->programmeImageRepository->fillThumbIfEmpty($image, $thumbFilename) === false) {
            // a concurrent request stored its thumb first, serve that one and keep a single file referenced
            $this->saveFileHandler->deleteFile($thumbFilename);
            $storedThumbFilename = $this->programmeImageRepository->findOneBy(['id' => $image->id])?->thumbFilename;

            return $storedThumbFilename === null ? null : $this->open($image, $storedThumbFilename);
        }

        $image->thumbFilename = $thumbFilename;

        return $this->open($image, $thumbFilename);
    }

    /**
     * @param list<ProgrammeImage> $images
     */
    private function positionOf(array $images, ProgrammeImage $image): int
    {
        foreach ($images as $position => $candidate) {
            if ($candidate->id === $image->id) {
                return $position;
            }
        }

        throw new LogicException('Image ' . $image->id . ' is missing in its own programme');
    }

    private function deleteStoredFile(string $filename): void
    {
        if ($this->isGeneratedFilename($filename) === false) {
            $this->logger->warning('Programme image file not deleted, unexpected filename', ['filename' => $filename]);

            return;
        }

        $this->saveFileHandler->deleteFile($filename);
    }

    private function open(ProgrammeImage $image, string $filename): ?File
    {
        if ($this->isGeneratedFilename($filename) === false) {
            $this->logUnreadable($image);

            return null;
        }

        try {
            return $this->saveFileHandler->getFile($filename);
        } catch (Throwable) {
            $this->logUnreadable($image);

            return null;
        }
    }

    private function read(ProgrammeImage $image, string $filename): ?string
    {
        if ($this->isGeneratedFilename($filename) === false) {
            $this->logUnreadable($image);

            return null;
        }

        try {
            return $this->saveFileHandler->getFile($filename)->stream->getContents();
        } catch (Throwable) {
            $this->logUnreadable($image);

            return null;
        }
    }

    /**
     * The local file handler joins the name into a path, so only names that getNewFilename() makes are opened.
     */
    private function isGeneratedFilename(string $filename): bool
    {
        return preg_match('/^[a-f0-9]{32}$/D', $filename) === 1;
    }

    private function logUnreadable(ProgrammeImage $image): void
    {
        $this->logger->warning('Programme image file unreadable', ['programmeImageId' => $image->id]);
    }
}
