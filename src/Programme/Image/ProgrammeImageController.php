<?php

declare(strict_types=1);

namespace kissj\Programme\Image;

use kissj\AbstractController;
use kissj\FileHandler\File;
use Psr\Http\Message\ResponseInterface as Response;

class ProgrammeImageController extends AbstractController
{
    public function __construct(
        private readonly ProgrammeImageRepository $programmeImageRepository,
        private readonly ProgrammeImageService $programmeImageService,
    ) {
    }

    public function full(Response $response, string $filename): Response
    {
        $image = $this->programmeImageRepository->findServableByFilename($filename);
        $file = $image === null ? null : $this->programmeImageService->openFull($image);

        return $file === null ? $response->withStatus(404) : $this->withImageHeaders($response, $file);
    }

    public function thumb(Response $response, string $filename): Response
    {
        $image = $this->programmeImageRepository->findServableByFilename($filename);
        if ($image === null) {
            return $response->withStatus(404);
        }

        $file = $this->programmeImageService->openThumb($image);
        if ($file === null) {
            // an over-cap, undecodable or missing original: without a cache every request would read it again
            return $response->withStatus(404)->withHeader('Cache-Control', 'public, max-age=3600');
        }

        return $this->withImageHeaders($response, $file);
    }

    /**
     * Images change only under new names, so any cache may keep them for good. No credentials are allowed:
     * the random filename is the only capability.
     */
    private function withImageHeaders(Response $response, File $file): Response
    {
        return $this->withUntrustedFileSecurityHeaders($response)
            ->withHeader('Content-Type', $file->mimeContentType)
            ->withHeader('Cache-Control', 'public, max-age=31536000, immutable')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withBody($file->stream);
    }
}
